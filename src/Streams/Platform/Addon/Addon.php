<?php namespace Streams\Platform\Addon;

use Illuminate\Foundation\Application;
use Streams\Platform\Traits\CallableTrait;
use Streams\Platform\Contract\PresenterInterface;

class Addon implements PresenterInterface
{
    use CallableTrait;

    protected $type = null;

    protected $slug = null;

    protected $path = null;

    protected $app;

    function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function isCore()
    {
        return str_contains($this->getPath(), 'core/addons');
    }

    public function getPath($path = null)
    {
        return $this->path . ($path ? '/' . ltrim($path, '/') : null);
    }

    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    public function getAbstract()
    {
        return "streams.{$this->getType()}.{$this->getSlug()}";
    }

    public function newPresenter()
    {
        return new AddonPresenter($this);
    }

    public function newServiceProvider()
    {
        return new AddonServiceProvider($this->app);
    }

    /*public static function stub()
    {
        return new static(Bootstrap::application());
    }*/
}
