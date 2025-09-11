<?php

namespace Modules\CashVan\Http\Controllers;

use Datatables;

use App\BusinessLocation;

use App\Product;
use App\Transaction;
use App\PurchaseLine;
use App\SellingPriceGroup;

use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use App\Variation;
use App\VariationLocationDetails;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use DB;
use Modules\CashVan\Entities\MainStock;
use Modules\CashVan\Entities\Van;
use Modules\CashVan\Entities\VanStock;
use PhpParser\Node\Stmt\Else_;
use Spatie\Activitylog\Models\Activity;

use function PHPUnit\Framework\isNull;

class VanStockController extends Controller
{
    /*
      * All Utils instance.
     */
    protected $productUtil;

    protected $transactionUtil;

    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param  ProductUtils  $product
     * @return void
     */
    public function __construct(ProductUtil $productUtil, TransactionUtil $transactionUtil, ModuleUtil $moduleUtil)
    {
        $this->productUtil = $productUtil;
        $this->transactionUtil = $transactionUtil;
        $this->moduleUtil = $moduleUtil;
    }
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('cashvan::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create($id)
    {
        if (! auth()->user()->can('van_stock.create')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');


        $business_locations = BusinessLocation::forDropdown($business_id);

        return view('cashvan::van_stock.create')->with(compact([
            'id',
            'business_locations'
        ]));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request, $id)
    {
        if (! auth()->user()->can('van_stock.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = $request->session()->get('user.business_id');

            DB::beginTransaction();

            $input_data = $request->only(['location_id', 'ref_no', 'transaction_date', 'additional_notes', 'final_total']);
            $status = 'completed';
            $user_id = $request->session()->get('user.id');
            $van_id = $id;
            $input_data['total_before_tax'] = $input_data['final_total'];

            $input_data['type'] = 'sell_transfer';
            $input_data['business_id'] = $business_id;
            $input_data['created_by'] = $user_id;
            $input_data['transaction_date'] = $this->productUtil->uf_date($input_data['transaction_date'], true);
            $input_data['payment_status'] = 'paid';
            $input_data['status'] = $status == 'completed' ? 'final' : $status;

            //Update reference count
            $ref_count = $this->productUtil->setAndGetReferenceCount('stock_transfer');
            //Generate reference number
            if (empty($input_data['ref_no'])) {
                $input_data['ref_no'] = $this->productUtil->generateReferenceNumber('stock_transfer', $ref_count);
            }

            $products = $request->input('products');
            $sell_lines = [];
            $purchase_lines = [];

            if (! empty($products)) {
                foreach ($products as $product) {
                    $sell_line_arr = [
                        'product_id' => $product['product_id'],
                        'variation_id' => $product['variation_id'],
                        'quantity' => $this->productUtil->num_uf($product['quantity']),
                        'item_tax' => 0,
                        'tax_id' => null,
                    ];

                    if (! empty($product['product_unit_id'])) {
                        $sell_line_arr['product_unit_id'] = $product['product_unit_id'];
                    }
                    if (! empty($product['sub_unit_id'])) {
                        $sell_line_arr['sub_unit_id'] = $product['sub_unit_id'];
                    }

                    $purchase_line_arr = $sell_line_arr;

                    if (! empty($product['base_unit_multiplier'])) {
                        $sell_line_arr['base_unit_multiplier'] = $product['base_unit_multiplier'];
                    }

                    $sell_line_arr['unit_price'] = $this->productUtil->num_uf($product['unit_price']);
                    $sell_line_arr['unit_price_inc_tax'] = $sell_line_arr['unit_price'];

                    $purchase_line_arr['purchase_price'] = $sell_line_arr['unit_price'];
                    $purchase_line_arr['purchase_price_inc_tax'] = $sell_line_arr['unit_price'];

                    if (! empty($product['lot_no_line_id'])) {
                        //Add lot_no_line_id to sell line
                        $sell_line_arr['lot_no_line_id'] = $product['lot_no_line_id'];

                        //Copy lot number and expiry date to purchase line
                        $lot_details = PurchaseLine::find($product['lot_no_line_id']);
                        $purchase_line_arr['lot_number'] = $lot_details->lot_number;
                        $purchase_line_arr['mfg_date'] = $lot_details->mfg_date;
                        $purchase_line_arr['exp_date'] = $lot_details->exp_date;
                    }

                    if (! empty($product['base_unit_multiplier'])) {
                        $purchase_line_arr['quantity'] = $purchase_line_arr['quantity'] * $product['base_unit_multiplier'];
                        $purchase_line_arr['purchase_price'] = $purchase_line_arr['purchase_price'] / $product['base_unit_multiplier'];
                        $purchase_line_arr['purchase_price_inc_tax'] = $purchase_line_arr['purchase_price_inc_tax'] / $product['base_unit_multiplier'];
                    }

                    if (isset($purchase_line_arr['sub_unit_id']) && $purchase_line_arr['sub_unit_id'] == $purchase_line_arr['product_unit_id']) {
                        unset($purchase_line_arr['sub_unit_id']);
                    }
                    unset($purchase_line_arr['product_unit_id']);

                    $sell_lines[] = $sell_line_arr;
                    $purchase_lines[] = $purchase_line_arr;
                }
            }
            $input_data['van_id'] = $van_id;
            //Create Sell Transfer transaction
            $sell_transfer = Transaction::create($input_data);

            //Create Purchase Transfer at van 
            $input_data['type'] = 'purchase_transfer';
            $input_data['transfer_parent_id'] = $sell_transfer->id;
            $input_data['status'] = $status == 'completed' ? 'received' : $status;
            $input_data['location_id'] = null;
            $purchase_transfer = Transaction::create($input_data);

            //Sell Product from first location
            if (! empty($sell_lines)) {
                $this->transactionUtil->createOrUpdateSellLines($sell_transfer, $sell_lines, null, false, null, [], false);
            }

            //Purchase product in second location
            if (! empty($purchase_lines)) {
                $purchase_transfer->purchase_lines()->createMany($purchase_lines);
            }

            //Decrease product stock from sell location
            //And increase product stock at purchase location
            if ($status == 'completed') {
                foreach ($products as $product) {
                    if ($product['enable_stock']) {
                        $decrease_qty = $this->productUtil
                            ->num_uf($product['quantity']);
                        if (! empty($product['base_unit_multiplier'])) {
                            $decrease_qty = $decrease_qty * $product['base_unit_multiplier'];
                        }

                        $this->productUtil->decreaseProductQuantity(
                            $product['product_id'],
                            $product['variation_id'],
                            $sell_transfer->location_id,
                            $decrease_qty,
                            0,
                            null,
                            true
                        );

                        $this->updateProductQuantityForVanStock(
                            $van_id,
                            $product['product_id'],
                            $product['variation_id'],
                            $decrease_qty,
                            0,
                            null,
                            false
                        );
                    }
                }

                //Adjust stock over selling if found
                $this->productUtil->adjustStockOverSelling($purchase_transfer);

                //Map sell lines with purchase lines
                $business = [
                    'id' => $business_id,
                    'accounting_method' => $request->session()->get('business.accounting_method'),
                    'location_id' => $sell_transfer->location_id,
                ];
                $this->transactionUtil->mapPurchaseSell($business, $sell_transfer->sell_lines, 'purchase', true, null, null, true);
            }

            $this->transactionUtil->activityLog($sell_transfer, 'added');

            $activities = Activity::forSubject($sell_transfer)
                ->with(['causer', 'subject'])
                ->latest()
                ->get();

            $location_details = ['sell' => $sell_transfer->location];


            $sell_transfer = Transaction::where('id', $sell_transfer->id)->with(
                'location',
                'sell_lines',
                'sell_lines.product',
                'sell_lines.variations',
                'sell_lines.variations.product_variation',
                'sell_lines.sub_unit',
                'sell_lines.product.unit'
            )
                ->first();
            $sell_transfer->delete_stock = 1;
            foreach ($sell_transfer->sell_lines as $key => $value) {
                if (! empty($value->sub_unit_id)) {
                    $formated_sell_line = $this->transactionUtil->recalculateSellLineTotals($business_id, $value);

                    $sell_transfer->sell_lines[$key] = $formated_sell_line;
                }
            }

            $receipt['html_content'] = view('cashvan::van_stock.receipts.receipt', compact('sell_transfer', 'activities', 'location_details'))->render();
            session()->flash('receipts', [$receipt]);
            $output = [
                'success' => 1,
                'msg' => __('lang_v1.added_success'),

            ];

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => $e->getMessage(),
            ];
        }

        return redirect('cashvan')->with('status', $output);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (! auth()->user()->can('van_stock.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $sell_transfer = Transaction::where('business_id', $business_id)
            ->where('type', 'sell_transfer')
            ->where('van_id', $id)
            ->orderBy('created_at', 'desc')
            ->first();



        $van_stock = VanStock::where('van_id', $id)->get();

        
        foreach ($van_stock as $sell_line) {
            $line = Transaction::join('transaction_sell_lines as tsl', 'transactions.id', '=', 'tsl.transaction_id')
                ->leftjoin('products as p', 'p.id', 'tsl.product_id')
                ->leftjoin('variations as v', 'v.id', 'tsl.variation_id')
                ->leftjoin('product_variations AS pv', 'v.product_variation_id', '=', 'pv.id')
                ->leftjoin('units as un', 'un.id', '=', 'tsl.sub_unit_id')
                ->where('transactions.van_id', $id)
                ->where('tsl.variation_id', $sell_line->variation_id)
                ->orderBy('tsl.created_at', 'desc')->select(
                    'p.name as product_name',
                    'p.type as product_type',
                    'pv.name as product_variation_name',
                    'v.name as variation_name',
                    'v.sub_sku as variation_sku',
                    'un.short_name as unit_short_name',
                    'un.base_unit_multiplier as unit_multiplier',
                    'tsl.unit_price_inc_tax as unit_price_inc_tax'
                )
                ->first();
            $line->product_quantity = $sell_line->qty_available;
            $sell_transfer->sell_lines[] = $line;
        }


        $purchase_transfer = Transaction::where('business_id', $business_id)
            ->where('transfer_parent_id', $sell_transfer->id)
            ->where('type', 'purchase_transfer')
            ->first();
        if ($sell_transfer->location) {
            $location_details = ['sell' => $sell_transfer->location];
        } else {
            $location_details = ['sell' => $purchase_transfer->location];
        }


        $activities = Activity::forSubject($sell_transfer)
            ->with(['causer', 'subject'])
            ->latest()
            ->get();
        return view('cashvan::cashvan.show')
            ->with(compact('sell_transfer', 'location_details', 'activities'));
    }


    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        if (! auth()->user()->can('van_stock.create')) {
            abort(403, 'Unauthorized action.');
        }
        if (auth()->user()->role_name != 'Admin') {
            $exists = Van::where('driver_id', auth()->user()->id)->exists();
            if ($exists) {
                return to_route('van_stock.create_order', $id);
            }
        }
        $business_id = request()->session()->get('user.business_id');

        $business_locations = BusinessLocation::forDropdown($business_id);

        $sell_transfer = Transaction::where('business_id', $business_id)
            ->where('type', 'sell_transfer')
            ->where('van_id', $id)
            ->first();

        if (!$sell_transfer) {
            return to_route('van_stock.create', $id);
        }

        $van_stock = VanStock::where('van_id', $id)->get();

        $products = [];
        foreach ($van_stock as $sell_line) {
            $line = Transaction::join('transaction_sell_lines as tsl', 'transactions.id', '=', 'tsl.transaction_id')
                ->where('transactions.van_id', $id)
                ->where('tsl.variation_id', $sell_line->variation_id)->orderBy('tsl.created_at', 'desc')->select('tsl.id as sell_line_id', 'tsl.sub_unit_id', 'tsl.lot_no_line_id', 'tsl.quantity')->first();

            $product = $this->productUtil->getDetailsFromVariation($sell_line->variation_id, $business_id, $sell_transfer->location_id, false, $id);

            $product->quantity_ordered = $sell_line->qty_available;
            $data = VariationLocationDetails::where('variation_id', $sell_line->variation_id)->where(
                'location_id',
                $sell_transfer->location_id
            )->first();
            $product->qty_available = (($data->qty_available - $data->qty_in_vans)   ??  0) + $product->quantity_ordered;

            $product->formatted_qty_available = $this->productUtil->num_f($product->qty_available);
            $product->sub_unit_id = $line->sub_unit_id;
            $product->transaction_sell_lines_id = $line->sell_line_id;
            $product->lot_no_line_id = $line->lot_no_line_id;

            $product->unit_details = $this->productUtil->getSubUnits($business_id, $product->unit_id);

            //Get lot number dropdown if enabled
            $lot_numbers = [];

            if (request()->session()->get('business.enable_lot_number') == 1 || request()->session()->get('business.enable_product_expiry') == 1) {
                $lot_number_obj = $this->transactionUtil->getLotNumbersFromVariation($sell_line->variation_id, $business_id, $sell_transfer->location_id, true);

                foreach ($lot_number_obj as $lot_number) {
                    $lot_number->qty_formated = $this->productUtil->num_f($lot_number->qty_available);
                    $lot_numbers[] = $lot_number;
                }
            }
            $product->lot_numbers = $lot_numbers;

            $products[] = $product;
        }


        return view('cashvan::van_stock.edit')->with(compact('sell_transfer', 'business_locations', 'products'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        if (! auth()->user()->can('van_stock.create')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $business_id = request()->session()->get('user.business_id');

            //Check if subscribed or not
            if (! $this->moduleUtil->isSubscribed($business_id)) {
                return $this->moduleUtil->expiredResponse(action([\App\Http\Controllers\StockTransferController::class, 'index']));
            }



            $old_sell_transfer = Transaction::where('business_id', $business_id)
                ->where('type', 'sell_transfer')
                ->findOrFail($id);


            $van_id = $old_sell_transfer->van_id;
            $user_id = $request->session()->get('user.id');
            $input_data = $request->only(['location_id', 'ref_no', 'transaction_date', 'additional_notes', 'final_total']);
            $input_data['van_id'] = $van_id;
            $input_data['created_by'] = $user_id;
            $status = 'completed';
            $input_data['business_id'] = $business_id;
            $input_data['created_by'] = $user_id;
            $input_data['transaction_date'] = $this->productUtil->uf_date($input_data['transaction_date'], true);
            $input_data['payment_status'] = 'paid';
            $input_data['status'] = $status == 'completed' ? 'final' : $status;
            //Update reference count
            $ref_count = $this->productUtil->setAndGetReferenceCount('stock_transfer');
            //Generate reference number
            if (empty($input_data['ref_no'])) {
                $input_data['ref_no'] = $this->productUtil->generateReferenceNumber('stock_transfer', $ref_count);
            }
            DB::beginTransaction();


            $input_data['total_before_tax'] = $input_data['final_total'];





            $products = $request->input('products');
            $purchases_transfer = [];
            $sells_transfer = [];
            if (! empty($products)) {
                foreach ($products as $product) {
                    $variation = Variation::where('id', $product['variation_id'])
                        ->where('product_id', $product['product_id'])
                        ->first();


                    $van_stock = VanStock::where('variation_id', $variation->id)
                        ->where('product_id', $product['product_id'])
                        ->where('product_variation_id', $variation->product_variation_id)
                        ->where('van_id', $van_id)
                        ->first();
                    if ($van_stock && $van_stock->qty_available > $product['quantity'] * $product['base_unit_multiplier']) {

                        $sell_line_arr = [
                            'product_id' => $product['product_id'],
                            'variation_id' => $product['variation_id'],
                            'quantity' => $this->productUtil->num_uf(($van_stock->qty_available / $product['base_unit_multiplier']) - $product['quantity']),
                            'item_tax' => 0,
                            'tax_id' => null,
                        ];

                        if (! empty($product['product_unit_id'])) {
                            $sell_line_arr['product_unit_id'] = $product['product_unit_id'];
                        }
                        if (! empty($product['sub_unit_id'])) {
                            $sell_line_arr['sub_unit_id'] = $product['sub_unit_id'];
                        }

                        $purchase_line_arr = $sell_line_arr;

                        if (! empty($product['base_unit_multiplier'])) {
                            $sell_line_arr['base_unit_multiplier'] = $product['base_unit_multiplier'];
                        }

                        $sell_line_arr['unit_price'] = $this->productUtil->num_uf($product['unit_price']);
                        $sell_line_arr['unit_price_inc_tax'] = $sell_line_arr['unit_price'];

                        $purchase_line_arr['purchase_price'] = $sell_line_arr['unit_price'];
                        $purchase_line_arr['purchase_price_inc_tax'] = $sell_line_arr['unit_price'];

                        if (! empty($product['lot_no_line_id'])) {
                            //Add lot_no_line_id to sell line
                            $sell_line_arr['lot_no_line_id'] = $product['lot_no_line_id'];

                            //Copy lot number and expiry date to purchase line
                            $lot_details = PurchaseLine::find($product['lot_no_line_id']);
                            $purchase_line_arr['lot_number'] = $lot_details->lot_number;
                            $purchase_line_arr['mfg_date'] = $lot_details->mfg_date;
                            $purchase_line_arr['exp_date'] = $lot_details->exp_date;
                        }

                        if (! empty($product['base_unit_multiplier'])) {
                            $purchase_line_arr['quantity'] = $purchase_line_arr['quantity'] * $product['base_unit_multiplier'];
                            $purchase_line_arr['purchase_price'] = $purchase_line_arr['purchase_price'] / $product['base_unit_multiplier'];
                            $purchase_line_arr['purchase_price_inc_tax'] = $purchase_line_arr['purchase_price_inc_tax'] / $product['base_unit_multiplier'];
                        }

                        if (isset($purchase_line_arr['sub_unit_id']) && $purchase_line_arr['sub_unit_id'] == $purchase_line_arr['product_unit_id']) {
                            unset($purchase_line_arr['sub_unit_id']);
                        }
                        unset($purchase_line_arr['product_unit_id']);

                        $sells_transfer['sell_lines'][] = $sell_line_arr;
                        $sells_transfer['purchase_lines'][] = $purchase_line_arr;
                    } elseif ($van_stock && $van_stock->qty_available < $product['quantity'] * $product['base_unit_multiplier']) {

                        $sell_line_arr = [
                            'product_id' => $product['product_id'],
                            'variation_id' => $product['variation_id'],
                            'quantity' => $this->productUtil->num_uf($product['quantity'] - ($van_stock->qty_available / $product['base_unit_multiplier'])),
                            'item_tax' => 0,
                            'tax_id' => null,
                        ];

                        if (! empty($product['product_unit_id'])) {
                            $sell_line_arr['product_unit_id'] = $product['product_unit_id'];
                        }
                        if (! empty($product['sub_unit_id'])) {
                            $sell_line_arr['sub_unit_id'] = $product['sub_unit_id'];
                        }

                        $purchase_line_arr = $sell_line_arr;

                        if (! empty($product['base_unit_multiplier'])) {
                            $sell_line_arr['base_unit_multiplier'] = $product['base_unit_multiplier'];
                        }

                        $sell_line_arr['unit_price'] = $this->productUtil->num_uf($product['unit_price']);
                        $sell_line_arr['unit_price_inc_tax'] = $sell_line_arr['unit_price'];

                        $purchase_line_arr['purchase_price'] = $sell_line_arr['unit_price'];
                        $purchase_line_arr['purchase_price_inc_tax'] = $sell_line_arr['unit_price'];

                        if (! empty($product['lot_no_line_id'])) {
                            //Add lot_no_line_id to sell line
                            $sell_line_arr['lot_no_line_id'] = $product['lot_no_line_id'];

                            //Copy lot number and expiry date to purchase line
                            $lot_details = PurchaseLine::find($product['lot_no_line_id']);
                            $purchase_line_arr['lot_number'] = $lot_details->lot_number;
                            $purchase_line_arr['mfg_date'] = $lot_details->mfg_date;
                            $purchase_line_arr['exp_date'] = $lot_details->exp_date;
                        }

                        if (! empty($product['base_unit_multiplier'])) {
                            $purchase_line_arr['quantity'] = $purchase_line_arr['quantity'] * $product['base_unit_multiplier'];
                            $purchase_line_arr['purchase_price'] = $purchase_line_arr['purchase_price'] / $product['base_unit_multiplier'];
                            $purchase_line_arr['purchase_price_inc_tax'] = $purchase_line_arr['purchase_price_inc_tax'] / $product['base_unit_multiplier'];
                        }

                        if (isset($purchase_line_arr['sub_unit_id']) && $purchase_line_arr['sub_unit_id'] == $purchase_line_arr['product_unit_id']) {
                            unset($purchase_line_arr['sub_unit_id']);
                        }
                        unset($purchase_line_arr['product_unit_id']);

                        $purchases_transfer['sell_lines'][] = $sell_line_arr;
                        $purchases_transfer["purchase_lines"][] = $purchase_line_arr;
                    } elseif (!$van_stock) {

                        $sell_line_arr = [
                            'product_id' => $product['product_id'],
                            'variation_id' => $product['variation_id'],
                            'quantity' => $this->productUtil->num_uf($product['quantity']),
                            'item_tax' => 0,
                            'tax_id' => null,
                        ];

                        if (! empty($product['product_unit_id'])) {
                            $sell_line_arr['product_unit_id'] = $product['product_unit_id'];
                        }
                        if (! empty($product['sub_unit_id'])) {
                            $sell_line_arr['sub_unit_id'] = $product['sub_unit_id'];
                        }

                        $purchase_line_arr = $sell_line_arr;

                        if (! empty($product['base_unit_multiplier'])) {
                            $sell_line_arr['base_unit_multiplier'] = $product['base_unit_multiplier'];
                        }

                        $sell_line_arr['unit_price'] = $this->productUtil->num_uf($product['unit_price']);
                        $sell_line_arr['unit_price_inc_tax'] = $sell_line_arr['unit_price'];

                        $purchase_line_arr['purchase_price'] = $sell_line_arr['unit_price'];
                        $purchase_line_arr['purchase_price_inc_tax'] = $sell_line_arr['unit_price'];

                        if (! empty($product['lot_no_line_id'])) {
                            //Add lot_no_line_id to sell line
                            $sell_line_arr['lot_no_line_id'] = $product['lot_no_line_id'];

                            //Copy lot number and expiry date to purchase line
                            $lot_details = PurchaseLine::find($product['lot_no_line_id']);
                            $purchase_line_arr['lot_number'] = $lot_details->lot_number;
                            $purchase_line_arr['mfg_date'] = $lot_details->mfg_date;
                            $purchase_line_arr['exp_date'] = $lot_details->exp_date;
                        }

                        if (! empty($product['base_unit_multiplier'])) {
                            $purchase_line_arr['quantity'] = $purchase_line_arr['quantity'] * $product['base_unit_multiplier'];
                            $purchase_line_arr['purchase_price'] = $purchase_line_arr['purchase_price'] / $product['base_unit_multiplier'];
                            $purchase_line_arr['purchase_price_inc_tax'] = $purchase_line_arr['purchase_price_inc_tax'] / $product['base_unit_multiplier'];
                        }

                        if (isset($purchase_line_arr['sub_unit_id']) && $purchase_line_arr['sub_unit_id'] == $purchase_line_arr['product_unit_id']) {
                            unset($purchase_line_arr['sub_unit_id']);
                        }
                        unset($purchase_line_arr['product_unit_id']);

                        $purchases_transfer['sell_lines'][] = $sell_line_arr;
                        $purchases_transfer["purchase_lines"][] = $purchase_line_arr;
                    }
                }
            }
            $sells_transfer_ids = [];
            if (!empty($sells_transfer)) {
                $input_data['type'] = 'sell_transfer';
                $input_data['location_id'] = null;
                $sell_transfer = Transaction::create($input_data);
                $input_data['transfer_parent_id'] = $sell_transfer->id;
                $input_data['status'] = $status == 'completed' ? 'received' : $status;
                $input_data['location_id'] = $old_sell_transfer->location_id;
                $input_data['type'] = 'purchase_transfer';
                $purchase_transfer = Transaction::create($input_data);
                $sells_transfer_ids[] = $purchase_transfer->id;
                if (! empty($sells_transfer['sell_lines'])) {
                    $this->transactionUtil->createOrUpdateSellLines($sell_transfer, $sells_transfer['sell_lines'], null);
                }

                //Purchase product in second location
                if (! empty($sells_transfer['purchase_lines'])) {
                    $purchase_transfer->purchase_lines()->createMany($sells_transfer['purchase_lines']);
                }

                //Adjust stock over selling if found
                $this->productUtil->adjustStockOverSelling($purchase_transfer);

                //Map sell lines with purchase lines
                $business = [
                    'id' => $business_id,
                    'accounting_method' => $request->session()->get('business.accounting_method'),
                    'location_id' => $sell_transfer->location_id,
                ];
                $this->transactionUtil->mapPurchaseSell($business, $sell_transfer->sell_lines, 'purchase', true, null, null, true);
                $this->transactionUtil->activityLog($sell_transfer, 'added');
            }

            if (!empty($purchases_transfer)) {

                $input_data['type'] = 'sell_transfer';
                $input_data['location_id'] =  $old_sell_transfer->location_id;
                $input_data['status'] = 'final';
                $sell_transfer = Transaction::create($input_data);
                $sells_transfer_ids[] = $sell_transfer->id;
                $input_data['transfer_parent_id'] = $sell_transfer->id;
                $input_data['status'] = $status == 'completed' ? 'received' : $status;
                $input_data['location_id'] = null;
                $input_data['type'] = 'purchase_transfer';
                $purchase_transfer = Transaction::create($input_data);

                if (! empty($purchases_transfer['sell_lines'])) {
                    $this->transactionUtil->createOrUpdateSellLines($sell_transfer, $purchases_transfer['sell_lines'], null);
                }

                //Purchase product in second location
                if (! empty($purchases_transfer['purchase_lines'])) {
                    $purchase_transfer->purchase_lines()->createMany($purchases_transfer['purchase_lines']);
                }
                //Adjust stock over selling if found
                $this->productUtil->adjustStockOverSelling($purchase_transfer);

                //Map sell lines with purchase lines
                $business = [
                    'id' => $business_id,
                    'accounting_method' => $request->session()->get('business.accounting_method'),
                    'location_id' => $sell_transfer->location_id,
                ];
                $this->transactionUtil->mapPurchaseSell($business, $sell_transfer->sell_lines, 'purchase', true, null, null, true);

                $this->transactionUtil->activityLog($sell_transfer, 'added');
            }
            //Decrease product stock from sell location
            //And increase product stock at purchase location
            if ($status == 'completed') {
                if (isset($sell_transfer) || isset($purchase_transfer)) {
                    foreach ($products as $product) {
                        if ($product['enable_stock']) {
                            $variation = Variation::where('id', $product['variation_id'])
                                ->where('product_id', $product['product_id'])
                                ->first();

                            //Add quantity in VanStock
                            $van_stock = VanStock::where('variation_id', $variation->id)
                                ->where('product_id', $product['product_id'])
                                ->where('product_variation_id', $variation->product_variation_id)
                                ->where('van_id', $van_id)
                                ->first();
                            $decrease_qty = $this->productUtil
                                ->num_uf($product['quantity']);
                            if (! empty($product['base_unit_multiplier'])) {
                                $decrease_qty = $decrease_qty * $product['base_unit_multiplier'];
                            }



                            if ($van_stock) {
                                $this->productUtil->decreaseProductQuantity(
                                    $product['product_id'],
                                    $product['variation_id'],
                                    $sell_transfer->location_id ?? $purchase_transfer->location_id,
                                    $decrease_qty,
                                    $van_stock->qty_available,
                                    null,
                                    true
                                );
                            } else {
                                $this->productUtil->decreaseProductQuantity(
                                    $product['product_id'],
                                    $product['variation_id'],
                                    $sell_transfer->location_id ?? $purchase_transfer->location_id,
                                    $decrease_qty,
                                    0,
                                    null,
                                    true
                                );
                            }


                            $this->updateProductQuantityForVanStock(
                                $van_id,
                                $product['product_id'],
                                $product['variation_id'],
                                $decrease_qty,
                                0,
                                null,
                                false,
                                true
                            );
                        }
                    }
                }
            }

            $stock_changed = Transaction::whereIn('id', $sells_transfer_ids)
                ->get();
            $receipts = [];
            foreach ($stock_changed as $sell_transfer) {
                if ($sell_transfer->type == "purchase_transfer") {
                    $sell_transfer = Transaction::where('id', $sell_transfer->transfer_parent_id)->with(
                        'location',
                        'purchase_lines',
                        'purchase_lines.product',
                        'purchase_lines.variations',
                        'purchase_lines.variations.product_variation',
                        'purchase_lines.sub_unit',
                        'purchase_lines.product.unit'
                    )->first();



                    $activities = Activity::forSubject($sell_transfer)
                        ->with(['causer', 'subject'])
                        ->latest()
                        ->get();


                    $location_details = ['sell' => $old_sell_transfer->location];
                    $receipts[]['html_content'] = view('cashvan::van_stock.receipts.receipt', compact('sell_transfer', 'activities', 'location_details'))->render();
                } elseif ($sell_transfer->type == "sell_transfer") {

                    $sell_transfer->load(
                        'location',
                        'sell_lines',
                        'sell_lines.product',
                        'sell_lines.variations',
                        'sell_lines.variations.product_variation',
                        'sell_lines.sub_unit',
                        'sell_lines.product.unit'
                    );
                    $sell_transfer->delete_stock = 1;



                    $activities = Activity::forSubject($sell_transfer)
                        ->with(['causer', 'subject'])
                        ->latest()
                        ->get();

                    $location_details = ['sell' => $old_sell_transfer->location];
                    $receipts[]['html_content'] = view('cashvan::van_stock.receipts.receipt', compact('sell_transfer', 'activities', 'location_details'))->render();
                }
            }

            session()->flash('receipts', $receipts);
            $output = [
                'success' => 1,
                'msg' => __('lang_v1.updated_succesfully'),
            ];

            DB::commit();
        } catch (\Exception $e) {

            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => $e->getMessage(),
            ];
        }
        return redirect('cashvan')->with('status', $output);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function deleteStock($id, $data = [], $location_id)
    {

        $business_id = request()->session()->get('user.business_id');

        //Check if subscribed or not
        if (! $this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse(action([\App\Http\Controllers\StockTransferController::class, 'index']));
        }

        $van_id = $id;
        $user_id = request()->session()->get('user.id');
        $input_data = [];

        $input_data['van_id'] = $van_id;
        $input_data['created_by'] = $user_id;
        $status = 'completed';
        $input_data['business_id'] = $business_id;
        $input_data['created_by'] = $user_id;
        $time_format = 'h:i A';
        if (session('business.time_format') == 24) {
            $time_format = 'H:i';
        }

        $input_data['transaction_date'] = $this->productUtil->uf_date(
            Carbon::now()->format(session('business.date_format') . ' ' . $time_format),
            true
        );
        $input_data['payment_status'] = 'paid';
        $input_data['status'] = $status == 'completed' ? 'final' : $status;
        //Update reference count
        $ref_count = $this->productUtil->setAndGetReferenceCount('stock_transfer');
        //Generate reference number

        $input_data['ref_no'] = $this->productUtil->generateReferenceNumber('stock_transfer', $ref_count);




        $input_data['total_before_tax'] = 0;


        $products = $data['products'];


        $sells_transfer = [];
        if (! empty($products)) {
            foreach ($products as $product) {
                $variation = Variation::where('id', $product['variation_id'])
                    ->where('product_id', $product['product_id'])
                    ->first();


                $van_stock = VanStock::where('variation_id', $variation->id)
                    ->where('product_id', $product['product_id'])
                    ->where('product_variation_id', $variation->product_variation_id)
                    ->where('van_id', $van_id)
                    ->first();
                if ($van_stock && $van_stock->qty_available > $product['quantity'] * $product['base_unit_multiplier']) {

                    $sell_line_arr = [
                        'product_id' => $product['product_id'],
                        'variation_id' => $product['variation_id'],
                        'quantity' => $this->productUtil->num_uf(($van_stock->qty_available / $product['base_unit_multiplier']) - $product['quantity']),
                        'item_tax' => 0,
                        'tax_id' => null,
                    ];

                    if (! empty($product['product_unit_id'])) {
                        $sell_line_arr['product_unit_id'] = $product['product_unit_id'];
                    }
                    if (! empty($product['sub_unit_id'])) {
                        $sell_line_arr['sub_unit_id'] = $product['sub_unit_id'];
                    }

                    $purchase_line_arr = $sell_line_arr;

                    if (! empty($product['base_unit_multiplier'])) {
                        $sell_line_arr['base_unit_multiplier'] = $product['base_unit_multiplier'];
                    }

                    $sell_line_arr['unit_price'] = $this->productUtil->num_uf($product['unit_price']);
                    $sell_line_arr['unit_price_inc_tax'] = $sell_line_arr['unit_price'];

                    $purchase_line_arr['purchase_price'] = $sell_line_arr['unit_price'];
                    $purchase_line_arr['purchase_price_inc_tax'] = $sell_line_arr['unit_price'];

                    if (! empty($product['lot_no_line_id'])) {
                        //Add lot_no_line_id to sell line
                        $sell_line_arr['lot_no_line_id'] = $product['lot_no_line_id'];

                        //Copy lot number and expiry date to purchase line
                        $lot_details = PurchaseLine::find($product['lot_no_line_id']);
                        $purchase_line_arr['lot_number'] = $lot_details->lot_number;
                        $purchase_line_arr['mfg_date'] = $lot_details->mfg_date;
                        $purchase_line_arr['exp_date'] = $lot_details->exp_date;
                    }

                    if (! empty($product['base_unit_multiplier'])) {
                        $purchase_line_arr['quantity'] = $purchase_line_arr['quantity'] * $product['base_unit_multiplier'];
                        $purchase_line_arr['purchase_price'] = $purchase_line_arr['purchase_price'] / $product['base_unit_multiplier'];
                        $purchase_line_arr['purchase_price_inc_tax'] = $purchase_line_arr['purchase_price_inc_tax'] / $product['base_unit_multiplier'];
                    }

                    if (isset($purchase_line_arr['sub_unit_id']) && $purchase_line_arr['sub_unit_id'] == $purchase_line_arr['product_unit_id']) {
                        unset($purchase_line_arr['sub_unit_id']);
                    }
                    unset($purchase_line_arr['product_unit_id']);

                    $sells_transfer['sell_lines'][] = $sell_line_arr;
                    $sells_transfer['purchase_lines'][] = $purchase_line_arr;
                }
            }
        }

        if (!empty($sells_transfer)) {
            $input_data['type'] = 'sell_transfer';
            $input_data['location_id'] = null;
            $sell_transfer = Transaction::create($input_data);
            $input_data['transfer_parent_id'] = $sell_transfer->id;
            $input_data['status'] = $status == 'completed' ? 'received' : $status;
            $input_data['location_id'] = $location_id;
            $input_data['type'] = 'purchase_transfer';
            $purchase_transfer = Transaction::create($input_data);
            if (! empty($sells_transfer['sell_lines'])) {
                $this->transactionUtil->createOrUpdateSellLines($sell_transfer, $sells_transfer['sell_lines'], null);
            }

            //Purchase product in second location
            if (! empty($sells_transfer['purchase_lines'])) {
                $purchase_transfer->purchase_lines()->createMany($sells_transfer['purchase_lines']);
            }

            //Adjust stock over selling if found
            $this->productUtil->adjustStockOverSelling($purchase_transfer);

            //Map sell lines with purchase lines
            $business = [
                'id' => $business_id,
                'accounting_method' => request()->session()->get('business.accounting_method'),
                'location_id' => $sell_transfer->location_id,
            ];
            $this->transactionUtil->mapPurchaseSell($business, $sell_transfer->sell_lines, 'purchase', true, null, null, true);
            $this->transactionUtil->activityLog($sell_transfer, 'added');
        }


        //Decrease product stock from sell location
        //And increase product stock at purchase location
        if ($status == 'completed') {

            foreach ($products as $product) {
                if ($product['enable_stock']) {
                    $variation = Variation::where('id', $product['variation_id'])
                        ->where('product_id', $product['product_id'])
                        ->first();

                    //Add quantity in VanStock
                    $van_stock = VanStock::where('variation_id', $variation->id)
                        ->where('product_id', $product['product_id'])
                        ->where('product_variation_id', $variation->product_variation_id)
                        ->where('van_id', $van_id)
                        ->first();
                    $decrease_qty = $this->productUtil
                        ->num_uf($product['quantity']);
                    if (! empty($product['base_unit_multiplier'])) {
                        $decrease_qty = $decrease_qty * $product['base_unit_multiplier'];
                    }



                    if ($van_stock) {
                        $this->productUtil->decreaseProductQuantity(
                            $product['product_id'],
                            $product['variation_id'],
                            $location_id,
                            $decrease_qty,
                            $van_stock->qty_available,
                            null,
                            true
                        );
                    } else {
                        $this->productUtil->decreaseProductQuantity(
                            $product['product_id'],
                            $product['variation_id'],
                            $location_id,
                            $decrease_qty,
                            0,
                            null,
                            true
                        );
                    }


                    $this->updateProductQuantityForVanStock(
                        $van_id,
                        $product['product_id'],
                        $product['variation_id'],
                        $decrease_qty,
                        0,
                        null,
                        false,
                        true
                    );
                }
            }
        }






        return true;
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        if (! auth()->user()->can('van_stock.delete')) {
            abort(403, 'Unauthorized action.');
        }
        try {

            if (request()->ajax()) {
                $business_id = request()->session()->get('user.business_id');
                $data = [];
                $sell_transfer = Transaction::where('business_id', $business_id)
                    ->where('type', 'sell_transfer')
                    ->where('van_id', $id)
                    ->first();

                if (!$sell_transfer) {
                    return $output = [
                        'success' => 0,
                        'msg' => __('cashvan::stock.already_empty'),
                    ];
                }
                $van_stock = VanStock::where('van_id', $id)->get();

                $products = [];

                foreach ($van_stock as $sell_line) {
                    $product = $this->productUtil->getDetailsFromVariation($sell_line->variation_id, $business_id, $sell_transfer->location_id, false, $id);
                    $product->quantity = 0;
                    $product->base_unit_multiplier = 1;
                    $products[] = $product;
                }


                if (!empty($products)) {
                    $data['products'] = $products;
                    $delete = $this->deleteStock($id, $data, $sell_transfer->location_id);

                    if ($delete) {
                        return $output = [
                            'success' => 1,
                            'msg' => __('lang_v1.deleted_success'),
                        ];
                    }
                }

                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
    }
    /**
     * Checks if products has manage stock enabled then Updates quantity for product and its
     * variations
     *
     * @param $van_id
     * @param $product_id
     * @param $variation_id
     * @param $new_quantity
     * @param $old_quantity = 0
     * @param $number_format = null
     * @param $uf_data = true, if false it will accept numbers in database format
     * @return bool
     */
    private function updateProductQuantityForVanStock($van_id, $product_id, $variation_id, $new_quantity, $old_quantity = 0, $number_format = null, $uf_data = true, $for_update = false)
    {
        if ($uf_data) {
            $qty_difference = $this->num_uf($new_quantity, $number_format) - $this->num_uf($old_quantity, $number_format);
        } else {
            $qty_difference = $new_quantity - $old_quantity;
        }

        $product = Product::find($product_id);


        //Check if stock is enabled or not.
        if ($product->enable_stock == 1) {
            $variation = Variation::where('id', $variation_id)
                ->where('product_id', $product_id)
                ->first();

            //Add quantity in VanStock
            $van_stock = VanStock::where('variation_id', $variation->id)
                ->where('product_id', $product_id)
                ->where('product_variation_id', $variation->product_variation_id)
                ->where('van_id', $van_id)
                ->first();
            if ($for_update == true && !empty($van_stock)) {
                $qty_difference = $this->num_uf($new_quantity, $number_format) - $this->num_uf($van_stock->qty_available, $number_format);
            }

            if (empty($van_stock)) {
                $van_stock = new VanStock();
                $van_stock->variation_id = $variation->id;
                $van_stock->product_id = $product_id;
                $van_stock->van_id = $van_id;
                $van_stock->product_variation_id = $variation->product_variation_id;
                $van_stock->qty_available = 0;
            }

            $van_stock->qty_available += $qty_difference;
            $van_stock->save();
        }

        return $van_stock;
    }

    /**
     * Checks if products has manage stock enabled then Updates quantity for product and its
     * variations
     *
     * @param $van_id
     * @param $product_id
     * @param $variation_id
     * @param $new_quantity
     * @param $old_quantity = 0
     * @param $number_format = null
     * @param $uf_data = true, if false it will accept numbers in database format
     * @return bool
     */
    private function updateProductQuantityForVanStockOrder($van_id, $product_id, $variation_id, $new_quantity, $old_quantity = 0, $number_format = null, $uf_data = true, $for_update = false)
    {
        if ($uf_data) {
            $qty_difference = $this->num_uf($new_quantity, $number_format) - $this->num_uf($old_quantity, $number_format);
        } else {
            $qty_difference = $new_quantity - $old_quantity;
        }

        $product = Product::find($product_id);


        //Check if stock is enabled or not.
        if ($product->enable_stock == 1) {
            $variation = Variation::where('id', $variation_id)
                ->where('product_id', $product_id)
                ->first();

            //Add quantity in VanStock
            $van_stock = VanStock::where('variation_id', $variation->id)
                ->where('product_id', $product_id)
                ->where('product_variation_id', $variation->product_variation_id)
                ->where('van_id', $van_id)
                ->first();
            if ($for_update == true && !empty($van_stock)) {
                $qty_difference = $this->num_uf($new_quantity, $number_format) - $this->num_uf($van_stock->qty_available, $number_format);
            }

            if (empty($van_stock)) {
                $van_stock = new VanStock();
                $van_stock->variation_id = $variation->id;
                $van_stock->product_id = $product_id;
                $van_stock->van_id = $van_id;
                $van_stock->product_variation_id = $variation->product_variation_id;
                $van_stock->qty_available = 0;
            }

            $van_stock->qty_ordered += $qty_difference;
            $van_stock->save();
        }

        return $van_stock;
    }
    public function num_uf($input_number, $currency_details = null)
    {
        $thousand_separator = '';
        $decimal_separator = '';

        if (! empty($currency_details)) {
            $thousand_separator = $currency_details->thousand_separator;
            $decimal_separator = $currency_details->decimal_separator;
        } else {
            $thousand_separator = session()->has('currency') ? session('currency')['thousand_separator'] : '';
            $decimal_separator = session()->has('currency') ? session('currency')['decimal_separator'] : '';
        }

        $num = str_replace($thousand_separator, '', $input_number);
        $num = str_replace($decimal_separator, '.', $num);

        return (float) $num;
    }

    public function createMainStock(Request $request)
    {
        if (! auth()->user()->can('van_stock.create')) {
            abort(403, 'Unauthorized action.');
        }

        return view('cashvan::van_stock.create_main_stock');
    }

    public function saveMainStock(Request $request)
    {
        if (! auth()->user()->can('van_stock.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {

            MainStock::truncate();

            foreach ($request->products as $product) {

                MainStock::create([
                    'sub_unit_id' => $product['sub_unit_id'],
                    'unit_price' => $product['unit_price'],
                    'quantity' => $product['quantity'],
                    'variation_id' => $product['variation_id'],
                    'product_id' => $product['product_id'],
                ]);
            }

            $output = [
                'success' => 1,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {

            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => $e->getMessage(),
            ];
        }
        return redirect('cashvan')->with('status', $output);
    }


    /**
     * Shows product stock report
     *
     * @return \Illuminate\Http\Response
     */
    public function getStockReport(Request $request)
    {
        if (! auth()->user()->can('van_report.view')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = $request->session()->get('user.business_id');

        $selling_price_groups = SellingPriceGroup::where('business_id', $business_id)
            ->get();
        $allowed_selling_price_group = false;
        foreach ($selling_price_groups as $selling_price_group) {
            if (auth()->user()->can('selling_price_group.' . $selling_price_group->id)) {
                $allowed_selling_price_group = true;
                break;
            }
        }

        if ($request->ajax()) {
            $filters = request()->only([
                'van_id',

            ]);

            $query = Variation::join('products as p', 'p.id', '=', 'variations.product_id')
                ->join('units', 'p.unit_id', '=', 'units.id')
                ->join('van_stock as vs', 'variations.id', '=', 'vs.variation_id')
                ->leftjoin('vans as van', 'van.id', '=', 'vs.van_id')
                ->leftjoin('categories as c', 'p.category_id', '=', 'c.id')
                ->join('product_variations as pv', 'variations.product_variation_id', '=', 'pv.id')
                ->where('p.business_id', $business_id)
                ->whereIn('p.type', ['single', 'variable']);

            $location_filter = '';



            if (! empty($filters['van_id'])) {
                $van_id = $filters['van_id'];

                $query->where('vs.van_id', $van_id);

                $location_filter .= "AND transactions.van_id=$van_id";
            }


            $pl_query_string = $this->get_pl_quantity_sum_string('pl');



            $products = $query->select(
                // DB::raw("(SELECT SUM(quantity) FROM transaction_sell_lines LEFT JOIN transactions ON transaction_sell_lines.transaction_id=transactions.id WHERE transactions.status='final' $location_filter AND
                //     transaction_sell_lines.product_id=products.id) as total_sold"),

                DB::raw("(SELECT SUM(TSL.quantity - TSL.quantity_returned) FROM transactions 
                  JOIN transaction_sell_lines AS TSL ON transactions.id=TSL.transaction_id
                  WHERE transactions.status='final' AND transactions.type='sell' AND transactions.van_id=vs.van_id
                  AND TSL.variation_id=variations.id) as total_sold"),
                DB::raw("(SELECT SUM(IF(transactions.type='sell_transfer', TSL.quantity, 0) ) FROM transactions 
                  JOIN transaction_sell_lines AS TSL ON transactions.id=TSL.transaction_id
                  WHERE transactions.status='final' AND transactions.type='sell_transfer' AND transactions.van_id=vs.van_id AND (TSL.variation_id=variations.id)) as total_transfered"),
                DB::raw("(SELECT SUM(IF(transactions.type='stock_adjustment', SAL.quantity, 0) ) FROM transactions 
                  JOIN stock_adjustment_lines AS SAL ON transactions.id=SAL.transaction_id
                  WHERE transactions.type='stock_adjustment' 
                    AND (SAL.variation_id=variations.id)) as total_adjusted"),
                DB::raw("(SELECT SUM( COALESCE(pl.quantity - ($pl_query_string), 0) * purchase_price_inc_tax) FROM transactions 
                  JOIN purchase_lines AS pl ON transactions.id=pl.transaction_id
                  WHERE (transactions.status='received' OR transactions.type='purchase_return') 
                  AND (pl.variation_id=variations.id)) as stock_price"),
                DB::raw('SUM(vs.qty_available) as stock'),
                'variations.sub_sku as sku',
                'p.name as product',
                'p.type',
                'p.alert_quantity',
                'p.id as product_id',
                'units.short_name as unit',
                'p.enable_stock as enable_stock',
                'variations.sell_price_inc_tax as unit_price',
                'pv.name as product_variation',
                'variations.name as variation_name',
                'van.name as van_name',
                'van.id as van_id',
                'variations.id as variation_id',
                'c.name as category_name',
            )->groupBy('variations.id', 'vs.van_id');

            //To show stock details on view product modal


            $datatable = Datatables::of($products)
                ->editColumn('stock', function ($row) {
                    if ($row->enable_stock) {
                        $stock = $row->stock ? $row->stock : 0;

                        return  '<span class="current_stock" data-orig-value="' . (float) $stock . '" data-unit="' . $row->unit . '"> ' . $this->transactionUtil->num_f($stock, false, null, true) . '</span>' . ' ' . $row->unit;
                    } else {
                        return '--';
                    }
                })
                ->editColumn('product', function ($row) {
                    $name = $row->product;

                    return $name;
                })

                ->addColumn('variation', function ($row) {
                    $variation = '';
                    if ($row->type == 'variable') {
                        $variation .= $row->product_variation . '-' . $row->variation_name;
                    }

                    return $variation;
                })
                ->editColumn('total_sold', function ($row) {
                    $total_sold = 0;
                    if ($row->total_sold) {
                        $total_sold = (float) $row->total_sold;
                    }

                    return '<span data-is_quantity="true" class="total_sold" data-orig-value="' . $total_sold . '" data-unit="' . $row->unit . '" >' . $this->transactionUtil->num_f($total_sold, false, null, true) . '</span> ' . $row->unit;
                })
                ->editColumn('total_transfered', function ($row) {
                    $total_transfered = 0;
                    if ($row->total_transfered) {
                        $total_transfered = (float) $row->total_transfered;
                    }

                    return '<span class="total_transfered" data-orig-value="' . $total_transfered . '" data-unit="' . $row->unit . '" >' . $this->transactionUtil->num_f($total_transfered, false, null, true) . '</span> ' . $row->unit;
                })

                ->editColumn('total_adjusted', function ($row) {
                    $total_adjusted = 0;
                    if ($row->total_adjusted) {
                        $total_adjusted = (float) $row->total_adjusted;
                    }

                    return '<span class="total_adjusted" data-orig-value="' . $total_adjusted . '" data-unit="' . $row->unit . '" >' . $this->transactionUtil->num_f($total_adjusted, false, null, true) . '</span> ' . $row->unit;
                })
                ->editColumn('unit_price', function ($row) use ($allowed_selling_price_group) {
                    $html = '';
                    if (auth()->user()->can('access_default_selling_price')) {
                        $html .= $this->transactionUtil->num_f($row->unit_price, true);
                    }

                    if ($allowed_selling_price_group) {
                        $html .= ' <button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline  tw-dw-btn-primary tw-w-max btn-modal no-print" data-container=".view_modal" data-href="' . action([\App\Http\Controllers\ProductController::class, 'viewGroupPrice'], [$row->product_id]) . '">' . __('lang_v1.view_group_prices') . '</button>';
                    }

                    return $html;
                })
                ->editColumn('stock_price', function ($row) {
                    $html = '<span class="total_stock_price" data-orig-value="'
                        . $row->stock_price . '">' .
                        $this->transactionUtil->num_f($row->stock_price, true) . '</span>';

                    return $html;
                })
                ->editColumn('stock_value_by_sale_price', function ($row) {
                    $stock = $row->stock ? $row->stock : 0;
                    $unit_selling_price = (float) $row->group_price > 0 ? $row->group_price : $row->unit_price;
                    $stock_price = $stock * $unit_selling_price;

                    return  '<span class="stock_value_by_sale_price" data-orig-value="' . (float) $stock_price . '" > ' . $this->transactionUtil->num_f($stock_price, true) . '</span>';
                })
                ->addColumn('potential_profit', function ($row) {
                    $stock = $row->stock ? $row->stock : 0;
                    $unit_selling_price = (float) $row->group_price > 0 ? $row->group_price : $row->unit_price;
                    $stock_price_by_sp = $stock * $unit_selling_price;
                    $potential_profit = (float) $stock_price_by_sp - (float) $row->stock_price;

                    return  '<span class="potential_profit" data-orig-value="' . (float) $potential_profit . '" > ' . $this->transactionUtil->num_f($potential_profit, true) . '</span>';
                })
                ->setRowClass(function ($row) {
                    return $row->enable_stock && $row->stock <= $row->alert_quantity ? 'bg-danger' : '';
                })
                ->filterColumn('variation', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(pv.name, ''), '-', COALESCE(variations.name, '')) like ?", ["%{$keyword}%"]);
                })
                ->removeColumn('enable_stock')
                ->removeColumn('unit')
                ->removeColumn('id');

            $raw_columns = [
                'unit_price',
                'total_transfered',
                'total_sold',
                'total_adjusted',
                'stock',
                'stock_price',
                'stock_value_by_sale_price',
                'potential_profit',
            ];



            return $datatable->rawColumns($raw_columns)->make(true);
        }

        $vans = Van::pluck('name', 'id');
        return view('cashvan::van_stock.report')->with(compact('vans'));
    }

    public function get_pl_quantity_sum_string($table_name = '')
    {
        $table_name = ! empty($table_name) ? $table_name . '.' : '';
        $string = $table_name . 'quantity_sold + ' . $table_name . 'quantity_adjusted + ' . $table_name . 'quantity_returned + ' . $table_name . 'mfg_quantity_used';

        return $string;
    }


    public function getVanHistory(Request $request, $id)
    {
        if (! auth()->user()->can('van_stock.view_history')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        $types = [
            'sell_transfer' => __('cashvan::stock.sell_transfer'),
            'sell' => __('cashvan::stock.sell'),
            'purchase_transfer' => __('cashvan::stock.purchase_transfer'),
        ];
        if (request()->ajax()) {
            $transactions = $this->getVanHistoryList($business_id, $id);
            $location_name = Van::where('id', $id)->first()->van_locations->first()->name ?? "";

            if (! empty(request()->start_date) && ! empty(request()->end_date)) {
                $start = request()->start_date;
                $end = request()->end_date;
                $transactions->whereDate('transactions.transaction_date', '>=', $start)
                    ->whereDate('transactions.transaction_date', '<=', $end);
            }
            if (! empty(request()->type)) {
                $type = request()->type;
                $transactions->where('transactions.type', $type);
            }

            return Datatables::of($transactions)
                ->addColumn('action', function ($row) {
                    $html = '';
                    if (auth()->user()->can('van_stock.view')) {
                        if ($row->type == 'sell') {
                            $html .= '<a href="#" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline  tw-dw-btn-info btn-modal" data-href="' . action([\App\Http\Controllers\SellController::class, 'show'], [$row->id]) . '"  data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i>' . __('messages.view') . '</a>';
                        } else {
                            $html .= '<a href="#" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline  tw-dw-btn-info btn-modal" data-href="' . action([\Modules\CashVan\Http\Controllers\VanStockController::class, 'showHistory'], ['id' => $row->id, 'type' => $row->type]) . '"  data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i>' . __('messages.view') . '</a>';
                        }
                    }
                    return $html;
                })
                ->editColumn('transaction_date', '{{@format_datetime($transaction_date)}}')
                ->removeColumn('id')
                ->editColumn('ref_no', function ($row) {
                    return   $row->ref_no;
                })
                ->editColumn(
                    'final_total',
                    '@if($type=="sell")
                        <span class="final_total" data-orig-value="{{$final_total}}">@format_currency($final_total)</span> 
                    @endif'
                )
                ->editColumn('transaction_date', '{{@format_datetime($transaction_date)}}')

                ->editColumn(
                    'type',
                    '<span>{{__(\'cashvan::stock.\' . $type)}}
                    </span>'
                )
                ->editColumn('location_name', function ($row) use ($location_name) {
                    return $location_name;
                })


                ->setRowAttr([
                    'data-href' => function ($row) {
                        if (auth()->user()->can('purchase.view')) {
                            if ($row->type == 'sell') {
                                return action([\App\Http\Controllers\SellController::class, 'show'], [$row->id]);
                            } elseif (auth()->user()->can('van_stock.view_history')) {
                                return  action([\Modules\CashVan\Http\Controllers\VanStockController::class, 'showHistory'], ['id' => $row->id, 'type' => $row->type]);
                            }
                        } else {
                            return '';
                        }
                    },
                ])
                ->rawColumns(['final_total', 'action', 'type', 'ref_no'])
                ->make(true);
        }


        return view('cashvan::van_stock.history')->with(compact('id', 'types'));
    }

    private function getVanHistoryList($business_id, $van_id)
    {
        $transactions = Transaction::where('transactions.business_id', $business_id)
            ->where('transactions.van_id', $van_id)
            ->where(function ($q) {
                $q->whereNull('transactions.location_id')
                    ->orWhere('transactions.type', 'sell'); // Allow all sell types regardless of location_id
            })
            ->leftJoin('users as u', 'transactions.created_by', '=', 'u.id')
            ->select(
                'transactions.transaction_date',
                'transactions.id',
                DB::raw("CONCAT(COALESCE(u.surname, ''),' ',COALESCE(u.first_name, ''),' ',COALESCE(u.last_name,'')) as added_by"),
                'transactions.type',
                'transactions.ref_no',
                'transactions.final_total'
            );


        return $transactions;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showHistory($id, Request $request)
    {
        if (! auth()->user()->can('van_stock.view')) {
            abort(403, 'Unauthorized action.');
        }
        $type = $request->type;
        $business_id = request()->session()->get('user.business_id');
        if ($type == 'purchase_transfer') {
            $purchase = Transaction::where('id', $id)->first();
            $sell_transfer = Transaction::where('id', $purchase->transfer_parent_id)->with(
                'location',
                'purchase_lines',
                'purchase_lines.product',
                'purchase_lines.variations',
                'purchase_lines.variations.product_variation',
                'purchase_lines.sub_unit',
                'purchase_lines.product.unit'
            )
                ->first();
            $sell_transfer->delete_stock = 1;
        } elseif ($type == 'sell_transfer') {
            $sell_transfer = Transaction::where('id', $id)->with(
                'location',
                'sell_lines',
                'sell_lines.product',
                'sell_lines.variations',
                'sell_lines.variations.product_variation',
                'sell_lines.sub_unit',
                'sell_lines.product.unit'
            )
                ->first();
        }




        if ($sell_transfer->type == 'purchase_transfer') {
            $purchase_transfer = Transaction::where('business_id', $business_id)
                ->where('id', $id - 1)
                ->first();
        } else {
            $purchase_transfer = Transaction::where('business_id', $business_id)
                ->where('transfer_parent_id', $id)
                ->first();
        }


        if ($sell_transfer->location) {
            $location_details = ['sell' => $sell_transfer->location];
        } else {
            $location_details = ['sell' => $purchase_transfer->location];
        }



        $activities = Activity::forSubject($sell_transfer)
            ->with(['causer', 'subject'])
            ->latest()
            ->get();
        return view('cashvan::van_stock.show')
            ->with(compact('sell_transfer', 'location_details', 'activities'));
    }


    public function createStockOrder(Request $request, $id)
    {
        if (!auth()->user()->can('van_stock.create')) {
            abort(403, 'Unauthorized action.');
        }
        $sell_transfer=Transaction::where('van_id',$id)
        ->where('type','sell_transfer')
        ->where('status','pending')->exists();
        if($sell_transfer){
            return to_route('van_stock.edit.order', $id);
        }
        $location_id = Van::where('id', $id)->first()->van_locations->first()->id;
        $business_id = request()->session()->get('user.business_id');


        $business_locations = BusinessLocation::forDropdown($business_id);
        return view('cashvan::van_stock.create_stock_order')->with(compact('id', 'business_locations', 'location_id'));
    }
    public function storeVanOrder(Request $request, $id)
    {
        if (! auth()->user()->can('van_stock.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = $request->session()->get('user.business_id');

            DB::beginTransaction();

            $input_data = $request->only(['location_id', 'ref_no', 'transaction_date', 'additional_notes', 'final_total']);
            $status = 'pending';
            $user_id = $request->session()->get('user.id');
            $van_id = $id;
            $input_data['total_before_tax'] = $input_data['final_total'];

            $input_data['type'] = 'sell_transfer';
            $input_data['business_id'] = $business_id;
            $input_data['created_by'] = $user_id;
            $input_data['transaction_date'] = $this->productUtil->uf_date($input_data['transaction_date'], true);
            $input_data['payment_status'] = 'paid';
            $input_data['status'] = 'pending';

            //Update reference count
            $ref_count = $this->productUtil->setAndGetReferenceCount('stock_transfer');
            //Generate reference number
            if (empty($input_data['ref_no'])) {
                $input_data['ref_no'] = $this->productUtil->generateReferenceNumber('stock_transfer', $ref_count);
            }

            $products = $request->input('products');
            $sell_lines = [];
            $purchase_lines = [];

            if (! empty($products)) {
                foreach ($products as $product) {
                    $sell_line_arr = [
                        'product_id' => $product['product_id'],
                        'variation_id' => $product['variation_id'],
                        'quantity' => $this->productUtil->num_uf($product['quantity']),
                        'item_tax' => 0,
                        'tax_id' => null,
                    ];

                    if (! empty($product['product_unit_id'])) {
                        $sell_line_arr['product_unit_id'] = $product['product_unit_id'];
                    }
                    if (! empty($product['sub_unit_id'])) {
                        $sell_line_arr['sub_unit_id'] = $product['sub_unit_id'];
                    }

                    $purchase_line_arr = $sell_line_arr;

                    if (! empty($product['base_unit_multiplier'])) {
                        $sell_line_arr['base_unit_multiplier'] = $product['base_unit_multiplier'];
                    }

                    $sell_line_arr['unit_price'] = $this->productUtil->num_uf($product['unit_price']);
                    $sell_line_arr['unit_price_inc_tax'] = $sell_line_arr['unit_price'];

                    $purchase_line_arr['purchase_price'] = $sell_line_arr['unit_price'];
                    $purchase_line_arr['purchase_price_inc_tax'] = $sell_line_arr['unit_price'];

                    if (! empty($product['lot_no_line_id'])) {
                        //Add lot_no_line_id to sell line
                        $sell_line_arr['lot_no_line_id'] = $product['lot_no_line_id'];

                        //Copy lot number and expiry date to purchase line
                        $lot_details = PurchaseLine::find($product['lot_no_line_id']);
                        $purchase_line_arr['lot_number'] = $lot_details->lot_number;
                        $purchase_line_arr['mfg_date'] = $lot_details->mfg_date;
                        $purchase_line_arr['exp_date'] = $lot_details->exp_date;
                    }

                    if (! empty($product['base_unit_multiplier'])) {
                        $purchase_line_arr['quantity'] = $purchase_line_arr['quantity'] * $product['base_unit_multiplier'];
                        $purchase_line_arr['purchase_price'] = $purchase_line_arr['purchase_price'] / $product['base_unit_multiplier'];
                        $purchase_line_arr['purchase_price_inc_tax'] = $purchase_line_arr['purchase_price_inc_tax'] / $product['base_unit_multiplier'];
                    }

                    if (isset($purchase_line_arr['sub_unit_id']) && $purchase_line_arr['sub_unit_id'] == $purchase_line_arr['product_unit_id']) {
                        unset($purchase_line_arr['sub_unit_id']);
                    }
                    unset($purchase_line_arr['product_unit_id']);

                    $sell_lines[] = $sell_line_arr;
                    $purchase_lines[] = $purchase_line_arr;
                }
            }
            $input_data['van_id'] = $van_id;
            //Create Sell Transfer transaction
            $sell_transfer = Transaction::create($input_data);

            //Create Purchase Transfer at van 
            $input_data['type'] = 'purchase_transfer';
            $input_data['transfer_parent_id'] = $sell_transfer->id;
            $input_data['status'] = 'pending';
            $input_data['location_id'] = null;
            $purchase_transfer = Transaction::create($input_data);

            //Sell Product from first location
            if (! empty($sell_lines)) {
                $this->transactionUtil->createOrUpdateSellLines($sell_transfer, $sell_lines, null, false, null, [], false);
            }

            //Purchase product in second location
            if (! empty($purchase_lines)) {
                $purchase_transfer->purchase_lines()->createMany($purchase_lines);
            }

            //Decrease product stock from sell location
            //And increase product stock at purchase location
            if ($status == 'pending') {
                foreach ($products as $product) {
                    if ($product['enable_stock']) {
                        $decrease_qty = $this->productUtil
                            ->num_uf($product['quantity']);
                        if (! empty($product['base_unit_multiplier'])) {
                            $decrease_qty = $decrease_qty * $product['base_unit_multiplier'];
                        }

                        $this->productUtil->decreaseProductQuantity(
                            $product['product_id'],
                            $product['variation_id'],
                            $sell_transfer->location_id,
                            $decrease_qty,
                            0,
                            null,
                            true
                        );

                        $this->updateProductQuantityForVanStockOrder(
                            $van_id,
                            $product['product_id'],
                            $product['variation_id'],
                            $decrease_qty,
                            0,
                            null,
                            false
                        );
                    }
                }
                Van::where('id', $sell_transfer->van_id)->update([
                    'is_add_stock_order' => true
                ]);
                //Adjust stock over selling if found
                $this->productUtil->adjustStockOverSelling($purchase_transfer);

                //Map sell lines with purchase lines
                $business = [
                    'id' => $business_id,
                    'accounting_method' => $request->session()->get('business.accounting_method'),
                    'location_id' => $sell_transfer->location_id,
                ];
                $this->transactionUtil->mapPurchaseSell($business, $sell_transfer->sell_lines, 'purchase', true, null, null, true);
            }

            $this->transactionUtil->activityLog($sell_transfer, 'added');
            $output = [
                'success' => 1,
                'msg' => __('lang_v1.added_success'),

            ];

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => $e->getMessage(),
            ];
        }

        return redirect('cashvan')->with('status', $output);
    }

    public function showStockOrder(Request $request, $id)
    {
        if (!auth()->user()->can('stock.accept_on_order')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $sell_transfer = Transaction::where('business_id', $business_id)
            ->where('type', 'sell_transfer')
            ->where('status', 'pending')
            ->where('van_id', $id)
            ->first();
        $van_stock = VanStock::where('van_id', $id)->where('qty_ordered', '>', 0)->get();

        $products = [];
        foreach ($van_stock as $sell_line) {
            $line = Transaction::join('transaction_sell_lines as tsl', 'transactions.id', '=', 'tsl.transaction_id')
                ->where('transactions.status', 'pending')
                ->where('transactions.van_id', $id)
                ->where('tsl.variation_id', $sell_line->variation_id)->orderBy('tsl.created_at', 'desc')->select('tsl.id as sell_line_id', 'tsl.sub_unit_id', 'tsl.lot_no_line_id', 'tsl.quantity')->first();

            $product = $this->productUtil->getDetailsFromVariation($sell_line->variation_id, $business_id, $sell_transfer->location_id, false, $id);

            $product->quantity_ordered = $sell_line->qty_ordered;
            $data = VariationLocationDetails::where('variation_id', $sell_line->variation_id)->where(
                'location_id',
                $sell_transfer->location_id
            )->first();
            $product->qty_available = (($data->qty_available - $data->qty_in_vans)   ??  0) + $product->quantity_ordered;

            $product->formatted_qty_available = $this->productUtil->num_f($product->qty_available);
            $product->sub_unit_id = $line->sub_unit_id;
            $product->transaction_sell_lines_id = $line->sell_line_id;
            $product->lot_no_line_id = $line->lot_no_line_id;

            $product->unit_details = $this->productUtil->getSubUnits($business_id, $product->unit_id);

            //Get lot number dropdown if enabled
            $lot_numbers = [];

            if (request()->session()->get('business.enable_lot_number') == 1 || request()->session()->get('business.enable_product_expiry') == 1) {
                $lot_number_obj = $this->transactionUtil->getLotNumbersFromVariation($sell_line->variation_id, $business_id, $sell_transfer->location_id, true);

                foreach ($lot_number_obj as $lot_number) {
                    $lot_number->qty_formated = $this->productUtil->num_f($lot_number->qty_available);
                    $lot_numbers[] = $lot_number;
                }
            }
            $product->lot_numbers = $lot_numbers;

            $products[] = $product;
        }
        $business_locations = BusinessLocation::forDropdown($business_id);
        return view('cashvan::van_stock.view_stock_order')->with(compact('sell_transfer', 'business_locations', 'products'));
    }

    public function responseOnStockOrder(Request $request, $id)
    {
        if (!auth()->user()->can('stock.accept_on_order')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $business_id = $request->session()->get('user.business_id');

            DB::beginTransaction();
            $sell_transfer = Transaction::where('id', $id)->with(
                'location',
                'sell_lines',
                'sell_lines.product',
                'sell_lines.variations',
                'sell_lines.variations.product_variation',
                'sell_lines.sub_unit',
                'sell_lines.product.unit'
            )
                ->first();
            $van_stock = VanStock::where('van_id', $sell_transfer->van_id)->where('qty_ordered', '>', 0)->get();
            if ($request->type == 'accept') {
                Transaction::where('id', $sell_transfer->id)->update([
                    'status' => 'final'
                ]);
                Transaction::where('transfer_parent_id', $sell_transfer->id)->update([
                    'status'=>'received'
                ]);
                Van::where('id', $sell_transfer->van_id)->update([
                    'is_add_stock_order' => false
                ]);
                foreach ($van_stock as $stock) {
                    $stock->qty_available += $stock->qty_ordered;
                    $stock->qty_ordered = 0;
                    $stock->save();
                }
                $this->transactionUtil->activityLog($sell_transfer, 'added');

                $activities = Activity::forSubject($sell_transfer)
                    ->with(['causer', 'subject'])
                    ->latest()
                    ->get();

                $location_details = ['sell' => $sell_transfer->location];



                $sell_transfer->delete_stock = 1;
              

                $receipt['html_content'] = view('cashvan::van_stock.receipts.receipt', compact('sell_transfer', 'activities', 'location_details'))->render();
                session()->flash('receipts', [$receipt]);
                $output = [
                    'success' => 1,
                    'msg' => __('lang_v1.added_success'),

                ];
            }else{
                foreach ($van_stock as $stock) {
                    VariationLocationDetails::where('variation_id', $stock->variation_id)
                    ->where('location_id',$sell_transfer->location_id)
                    ->decrement('qty_in_vans',$stock->qty_ordered);
                    if($stock->qty_available==0){
                        $stock->delete();
                    }else{
                        $stock->qty_ordered = 0;
                        $stock->save();
                    }
                    
                }
                Transaction::where('id', $sell_transfer->id)->delete();
                Transaction::where('transfer_parent_id', $sell_transfer->id)->delete();
                Van::where('id', $sell_transfer->van_id)->update([
                    'is_add_stock_order' => false
                ]);
                $output = [
                    'success' => 1,
                    'msg' => __('lang_v1.deleted_success'),

                ];
            }


            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => $e->getMessage(),
            ];
        }

        return redirect('cashvan')->with('status', $output);
    }

    public function editStockOrder(Request $request,$id){
        if (!auth()->user()->can('van_stock.create')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        $sell_transfer = Transaction::where('business_id', $business_id)
            ->where('type', 'sell_transfer')
            ->where('status', 'pending')
            ->where('van_id', $id)
            ->first();
        $van_stock = VanStock::where('van_id', $id)->where('qty_ordered', '>', 0)->get();

        $products = [];
        foreach ($van_stock as $sell_line) {
            $line = Transaction::join('transaction_sell_lines as tsl', 'transactions.id', '=', 'tsl.transaction_id')
                ->where('transactions.status', 'pending')
                ->where('transactions.van_id', $id)
                ->where('tsl.variation_id', $sell_line->variation_id)->orderBy('tsl.created_at', 'desc')->select('tsl.id as sell_line_id', 'tsl.sub_unit_id', 'tsl.lot_no_line_id', 'tsl.quantity')->first();

            $product = $this->productUtil->getDetailsFromVariation($sell_line->variation_id, $business_id, $sell_transfer->location_id, false, $id);

            $product->quantity_ordered = $sell_line->qty_ordered;
            $data = VariationLocationDetails::where('variation_id', $sell_line->variation_id)->where(
                'location_id',
                $sell_transfer->location_id
            )->first();
            $product->qty_available = (($data->qty_available - $data->qty_in_vans)   ??  0) + $product->quantity_ordered;

            $product->formatted_qty_available = $this->productUtil->num_f($product->qty_available);
            $product->sub_unit_id = $line->sub_unit_id;
            $product->transaction_sell_lines_id = $line->sell_line_id;
            $product->lot_no_line_id = $line->lot_no_line_id;

            $product->unit_details = $this->productUtil->getSubUnits($business_id, $product->unit_id);

            //Get lot number dropdown if enabled
            $lot_numbers = [];

            if (request()->session()->get('business.enable_lot_number') == 1 || request()->session()->get('business.enable_product_expiry') == 1) {
                $lot_number_obj = $this->transactionUtil->getLotNumbersFromVariation($sell_line->variation_id, $business_id, $sell_transfer->location_id, true);

                foreach ($lot_number_obj as $lot_number) {
                    $lot_number->qty_formated = $this->productUtil->num_f($lot_number->qty_available);
                    $lot_numbers[] = $lot_number;
                }
            }
            $product->lot_numbers = $lot_numbers;

            $products[] = $product;
        }
        $business_locations = BusinessLocation::forDropdown($business_id);
        return view('cashvan::van_stock.edit_stock_order')->with(compact('sell_transfer', 'business_locations', 'products'));

    }
    public function updateStockOrder(Request $request,$id){
        if (! auth()->user()->can('van_stock.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
       
           

            $business_id = request()->session()->get('user.business_id');

            $sell_transfer = Transaction::where('business_id', $business_id)
                ->where('type', 'sell_transfer')
                ->findOrFail($id);
           
          

            $purchase_transfer = Transaction::where(
                'business_id',
                $business_id
            )
                ->where('transfer_parent_id', $id)
                ->where('type', 'purchase_transfer')
                ->with(['purchase_lines'])
                ->first();

            $status = 'pending';

            DB::beginTransaction();

            $input_data = $request->only(['transaction_date', 'additional_notes', 'shipping_charges', 'final_total']);
            $status = 'pending';

            $input_data['total_before_tax'] = $input_data['final_total'];

            $input_data['transaction_date'] = $this->productUtil->uf_date($input_data['transaction_date'], true);
           
            $input_data['status'] = $status == 'completed' ? 'final' : $status;

            $products = $request->input('products');
            $sell_lines = [];
            $purchase_lines = [];
            $edited_purchase_lines = [];
            if (! empty($products)) {
                foreach ($products as $product) {
                    $sell_line_arr = [
                        'product_id' => $product['product_id'],
                        'variation_id' => $product['variation_id'],
                        'quantity' => $this->productUtil->num_uf($product['quantity']),
                        'item_tax' => 0,
                        'tax_id' => null,
                    ];

                    if (! empty($product['product_unit_id'])) {
                        $sell_line_arr['product_unit_id'] = $product['product_unit_id'];
                    }
                    if (! empty($product['sub_unit_id'])) {
                        $sell_line_arr['sub_unit_id'] = $product['sub_unit_id'];
                    }

                    $purchase_line_arr = $sell_line_arr;

                    if (! empty($product['base_unit_multiplier'])) {
                        $sell_line_arr['base_unit_multiplier'] = $product['base_unit_multiplier'];
                    }

                    $sell_line_arr['unit_price'] = $this->productUtil->num_uf($product['unit_price']);
                    $sell_line_arr['unit_price_inc_tax'] = $sell_line_arr['unit_price'];

                    $purchase_line_arr['purchase_price'] = $sell_line_arr['unit_price'];
                    $purchase_line_arr['purchase_price_inc_tax'] = $sell_line_arr['unit_price'];
                    if (isset($product['transaction_sell_lines_id'])) {
                        $sell_line_arr['transaction_sell_lines_id'] = $product['transaction_sell_lines_id'];
                    }

                    if (! empty($product['lot_no_line_id'])) {
                        //Add lot_no_line_id to sell line
                        $sell_line_arr['lot_no_line_id'] = $product['lot_no_line_id'];

                        //Copy lot number and expiry date to purchase line
                        $lot_details = PurchaseLine::find($product['lot_no_line_id']);
                        $purchase_line_arr['lot_number'] = $lot_details->lot_number;
                        $purchase_line_arr['mfg_date'] = $lot_details->mfg_date;
                        $purchase_line_arr['exp_date'] = $lot_details->exp_date;
                    }

                    if (! empty($product['base_unit_multiplier'])) {
                        $purchase_line_arr['quantity'] = $purchase_line_arr['quantity'] * $product['base_unit_multiplier'];
                        $purchase_line_arr['purchase_price'] = $purchase_line_arr['purchase_price'] / $product['base_unit_multiplier'];
                        $purchase_line_arr['purchase_price_inc_tax'] = $purchase_line_arr['purchase_price_inc_tax'] / $product['base_unit_multiplier'];
                    }

                    if (isset($purchase_line_arr['sub_unit_id']) && $purchase_line_arr['sub_unit_id'] == $purchase_line_arr['product_unit_id']) {
                        unset($purchase_line_arr['sub_unit_id']);
                    }
                    unset($purchase_line_arr['product_unit_id']);

                    $sell_lines[] = $sell_line_arr;

                    $purchase_line = [];
                    //check if purchase_line for the variation exists else create new
                    foreach ($purchase_transfer->purchase_lines as $pl) {
                        if ($pl->variation_id == $purchase_line_arr['variation_id']) {
                            $pl->update($purchase_line_arr);
                            $edited_purchase_lines[] = $pl->id;
                            $purchase_line = $pl;
                            break;
                        }
                    }
                    if (empty($purchase_line)) {
                        $purchase_line = new PurchaseLine($purchase_line_arr);
                    }

                    $purchase_lines[] = $purchase_line;
                }
            }

            //Create Sell Transfer transaction
            $sell_transfer->update($input_data);
            $sell_transfer->save();

            

            //Create Purchase Transfer at transfer location
            $input_data['status'] = $status == 'completed' ? 'received' : $status;

            $purchase_transfer->update($input_data);
            $purchase_transfer->save();

            //Sell Product from first location
            if (! empty($sell_lines)) {
                $this->transactionUtil->createOrUpdateSellLines($sell_transfer, $sell_lines,null);
            }

            //Purchase product in second location
            if (! empty($purchase_lines)) {
                if (! empty($edited_purchase_lines)) {
                    PurchaseLine::where('transaction_id', $purchase_transfer->id)
                        ->whereNotIn('id', $edited_purchase_lines)
                        ->delete();
                }
                $purchase_transfer->purchase_lines()->saveMany($purchase_lines);
            }

            if ($status == 'pending') {
                foreach ($products as $product) {
                    if ($product['enable_stock']) {
                        $variation = Variation::where('id', $product['variation_id'])
                            ->where('product_id', $product['product_id'])
                            ->first();

                        //Add quantity in VanStock
                        $van_stock = VanStock::where('variation_id', $variation->id)
                            ->where('product_id', $product['product_id'])
                            ->where('product_variation_id', $variation->product_variation_id)
                            ->where('van_id', $sell_transfer->van_id)
                            ->first();
                       
                        $decrease_qty = $this->productUtil
                            ->num_uf($product['quantity']);
                        if (! empty($product['base_unit_multiplier'])) {
                            $decrease_qty = $decrease_qty * $product['base_unit_multiplier'];
                        }
                        if($van_stock){
                            $this->productUtil->decreaseProductQuantity(
                                $product['product_id'],
                                $product['variation_id'],
                                $sell_transfer->location_id,
                                $decrease_qty,
                                $van_stock->qty_ordered,
                                null,
                                true
                            );

                            $this->updateProductQuantityForVanStockOrder(
                                $sell_transfer->van_id,
                                $product['product_id'],
                                $product['variation_id'],
                                $decrease_qty,
                                $van_stock->qty_ordered,
                                null,
                                false
                            );
                        }else{
                            $this->productUtil->decreaseProductQuantity(
                                $product['product_id'],
                                $product['variation_id'],
                                $sell_transfer->location_id,
                                $decrease_qty,
                                0,
                                null,
                                true
                            );

                            $this->updateProductQuantityForVanStockOrder(
                                $sell_transfer->van_id,
                                $product['product_id'],
                                $product['variation_id'],
                                $decrease_qty,
                                0,
                                null,
                                false);
                        }
                       
                    }
                }
            
                //Adjust stock over selling if found
                $this->productUtil->adjustStockOverSelling($purchase_transfer);

                //Map sell lines with purchase lines
                $business = [
                    'id' => $business_id,
                    'accounting_method' => $request->session()->get('business.accounting_method'),
                    'location_id' => $sell_transfer->location_id,
                ];
                $this->transactionUtil->mapPurchaseSell($business, $sell_transfer->sell_lines, 'purchase', true, null, null, true);
            }

            

          

            $output = [
                'success' => 1,
                'msg' => __('lang_v1.updated_succesfully'),
            ];

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => $e->getMessage(),
            ];
        }

        return redirect('cashvan')->with('status', $output);
    }
}
