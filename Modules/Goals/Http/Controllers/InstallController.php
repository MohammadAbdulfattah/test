<?php

namespace Modules\Goals\Http\Controllers;

use App\System;
use Composer\Semver\Comparator;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class InstallController extends Controller
{
    public function __construct()
    {
        $this->module_name = 'Goals';
        $this->appVersion = config('goals.module_version');
        $this->module_display_name = 'Goals';
    }

    /**
     * Install
     *
     * @return Response
     */
    public function index()
    {
        if (! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '512M');

        //clear cache & config file
        config(['app.debug' => true]);
        Artisan::call('config:clear');

        //Check if Goals is installed or not.
        $is_installed = System::getProperty($this->module_name . '_version');
        if (! empty($is_installed)) {
            abort(404);
        }

        $this->install();
        return redirect()
            ->action('\App\Http\Controllers\Install\ModulesController@index')
            ->with('status', $output);
    }

    /**
     * Installing Goals Module
     */
    public function install()
    {
        try {


            $is_installed = System::getProperty($this->module_name . '_version');
            if (! empty($is_installed)) {
                abort(404);
            }

            DB::statement('SET default_storage_engine=INNODB;');
            Artisan::call('module:migrate', ['module' => 'Goals', '--force' => true]);
            Artisan::call('module:publish', ['module' => 'Goals']);
            System::addProperty($this->module_name . '_version', $this->appVersion);

            Artisan::call('passport:install --force');
            //Artisan::call('scribe:generate');

            DB::commit();

            $output = [
                'success' => 1,
                'msg' => 'Goals module installed succesfully',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => $e->getMessage(),
            ];
        }

        return redirect()
            ->action([\App\Http\Controllers\Install\ModulesController::class, 'index'])
            ->with('status', $output);
    }

    /**
     * Uninstall
     *
     * @return Response
     */
    public function uninstall()
    {
        if (! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            System::removeProperty($this->module_name . '_version');

            $output = [
                'success' => true,
                'msg' => __('lang_v1.success'),
            ];
        } catch (\Exception $e) {
            $output = [
                'success' => false,
                'msg' => $e->getMessage(),
            ];
        }

        return redirect()->back()->with(['status' => $output]);
    }

    /**
     * update module
     *
     * @return Response
     */
    public function update()
    {
        //Check if goals_version is same as appVersion then 404
        //If appVersion > goals_version - run update script.
        //Else there is some problem.
        if (! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', '512M');

            $goals_version = System::getProperty($this->module_name . '_version');

            if (Comparator::greaterThan($this->appVersion, $goals_version)) {
                ini_set('max_execution_time', 0);
                ini_set('memory_limit', '512M');

                //clear cache & config file
                config(['app.debug' => true]);
                Artisan::call('config:clear');
                //Artisan::call('cache:clear');

                DB::statement('SET default_storage_engine=INNODB;');
                Artisan::call('module:migrate', ['module' => 'Goals', '--force' => true]);
                Artisan::call('module:publish', ['module' => 'Goals']);
                System::setProperty($this->module_name . '_version', $this->appVersion);

                //Artisan::call('scribe:generate');
            } else {
                abort(404);
            }

            DB::commit();

            $output = [
                'success' => 1,
                'msg' => 'Goals module updated Succesfully to version ' . $this->appVersion . ' !!',
            ];

            return redirect()->back()->with(['status' => $output]);
        } catch (Exception $e) {
            DB::rollBack();
            exit($e->getMessage());
        }
    }
}
