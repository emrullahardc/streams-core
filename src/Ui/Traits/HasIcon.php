<?php

namespace Anomaly\Streams\Platform\Ui\Traits;

use Anomaly\Streams\Platform\Ui\Icon\IconRegistry;

/**
 * Trait HasHtmlAttributes
 *
 * @link   http://pyrocms.com/
 * @author Ryan Thompson <ryan@pyrocms.com>
 */
trait HasHtmlAttributes
{

    /**
     * The icon to display.
     *
     * @var string
     */
    protected $icon = null;

    /**
     * Get the icon.
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Set the icon.
     *
     * @param array $icon
     * @return $this
     */
    public function setIcon(string $icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Return icon HTML.
     *
     * @param array $icon
     * @return null|string
     */
    public function icon()
    {
        if (!$this->icon) {
            return null;
        }

        return '<i class="' . app(IconRegistry::class)->icon($this->icon) . '"></i>';
    }
}
