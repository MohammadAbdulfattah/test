<?php

namespace App\Http;

use Nwidart\Menus\Presenters\Presenter;

class AdminlteNavBarPresenter extends Presenter
{
    /**
     * {@inheritdoc}.
     */
    public function getOpenTagWrapper()
    {
        return '<ul class="nav navbar-nav">' . PHP_EOL;
    }

    /**
     * {@inheritdoc}.
     */
    public function getCloseTagWrapper()
    {
        return '</ul>' . PHP_EOL;
    }

    /**
     * {@inheritdoc}.
     */
    public function getMenuWithoutDropdownWrapper($item)
    {
        $activeClass = $this->getActiveState($item);
        return '<li' . $this->getActiveState($item) . '><a style="color: white;line-height: 1; display: flex; justify-content: space-between; align-items: center; background-color: ' . ($activeClass ? 'rgba(255, 255, 255, 0.2)' : 'transparent') . ';transition: background-color 0.3s;" onmouseover="this.style.backgroundColor=\'rgba(0, 0, 0, 0.3)\'" onmouseout="this.style.backgroundColor=\'transparent\'"href="' . $item->getUrl() . '" ' . $item->getAttributes() . '>' . $this->formatIcon($item->icon) . ' ' . $item->title . '</a></li>' . PHP_EOL;
    }

    public function getMenuWithoutDropdownWrapperr($item)
    {
        return '<li' . $this->getActiveState($item) . '><a style="color: black"href="' . $item->getUrl() . '" ' . $item->getAttributes() . '>' . $this->formatIcon($item->icon) . ' ' . $item->title . '</a></li>' . PHP_EOL;
    }
    /**
     * {@inheritdoc}.
     */
    public function getActiveState($item, $state = ' active')
    {
        return $item->isActive() ? $state : '';
    }

    /**
     * Get active state on child items.
     *
     * @param $item
     * @param  string  $state
     * @return null|string
     */
    public function getActiveStateOnChild($item, $state = ' active')
    {
        return $item->hasActiveOnChild() ? $state : '';
    }

    /**
     * {@inheritdoc}.
     */
    public function getDividerWrapper()
    {
        return '<div class="divider"></div>';
    }

    /**
     * {@inheritdoc}.
     */
    public function getHeaderWrapper($item)
    {
        return '<div class="dropdown-header">' . $item->title . '</div>';
    }

    /**
     * {@inheritdoc}.
     */
    public function getMenuWithDropDownWrapper($item)
    {
        $activeClass = $this->getActiveStateOnChild($item);
        $results = '';
        foreach ($item->getChilds() as $child) {
            if ($child->hidden()) {
                continue;
            }

            if ($child->hasSubMenu()) {
                $results .= $this->getMultiLevelDropdownWrapper($child);
            } elseif ($child->isHeader()) {
                $results .= $this->getHeaderWrapper($child);
            } elseif ($child->isDivider()) {
                $results .= $this->getDividerWrapper();
            } else {
                $results .= $this->getMenuWithoutDropdownWrapperr($child);
            }
        }

        return '<li class="dropdown' . $this->getActiveStateOnChild($item, ' active') . '">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" style="color: white; line-height: 1; display: flex; justify-content: space-between; align-items: center; background-color: ' . ($activeClass ? 'rgba(255, 255, 255, 0.2)' : 'transparent') . '; transition: background-color 0.3s;" onmouseover="this.style.backgroundColor=\'rgba(0, 0, 0, 0.3)\'" onmouseout="this.style.backgroundColor=\'' . ($activeClass ? 'rgba(255, 255, 255, 0.2)' : 'transparent') . '\'">
                    ' . $this->formatIcon($item->icon) . ' ' . $item->title . '
                    <b class="caret"></b>
                </a>
                <ul class="dropdown-menu">
                    ' . $results . '
                </ul>
            </li>' . PHP_EOL;
    }

    /**
     * Get child menu items.
     *
     * @param  \Nwidart\Menus\MenuItem  $item
     * @return string
     */
    public function getMultiLevelDropdownWrapper($item)
    {
        $activeClass = $this->getActiveStateOnChild($item);
        return '<li class="dropdown' . $this->getActiveStateOnChild($item, ' active') . '">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" style="color: white; line-height: 1; display: flex; justify-content: space-between; align-items: center; background-color: ' . ($activeClass ? 'rgba(255, 255, 255, 0.2)' : 'transparent') . '; transition: background-color 0.3s;" onmouseover="this.style.backgroundColor=\'rgba(0, 0, 0, 0.3)\'" onmouseout="this.style.backgroundColor=\'' . ($activeClass ? 'rgba(255, 255, 255, 0.2)' : 'transparent') . '\'">
                    ' . $this->formatIcon($item->icon) . ' ' . $item->title . '
                    <b class="caret pull-right caret-right"></b>
                </a>
                <ul class="dropdown-menu">
                    ' . $this->getChildMenuItems($item) . '
                </ul>
            </li>' . PHP_EOL;
    }

    /**
     * Returns the icon HTML. If the icon is SVG, it returns directly; 
     * otherwise, it assumes it's a FontAwesome class and wraps it in an <i> tag.
     *
     * @param string $icon
     * @return string
     */
    protected function formatIcon($icon)
    {
        if (strpos($icon, '<svg') !== false) {
            return $icon;
        } else {
            return '<i class="' . $icon . '"></i>';
        }
    }

    public function getArray($item)
    {
        if ($item->hasActiveOnChild()) {
            return '<path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M6 9l6 6l6 -6" />';
        } else {
            return '<path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M15 6l-6 6l6 6" />';
        }
    }
  
}
