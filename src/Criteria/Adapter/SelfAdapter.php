<?php

namespace Streams\Core\Criteria\Adapter;

use Illuminate\Support\Arr;

class SelfAdapter extends FileAdapter
{

    protected $data = [];
    protected $query = [];


    protected function readData()
    {
        $source = $this->stream->expandPrototypeAttribute('source');

        $file = base_path(trim($source->get('file', 'streams/' . $this->stream->handle . '.json'), '/\\'));

        $keyName = $this->stream->config('key_name', 'id');

        $this->data = Arr::get(json_decode(file_get_contents($file), true), 'data', []);

        array_walk($this->data, function ($item, $key) use ($keyName) {
            $this->data[$key] = [$keyName => $key] + $item;
        });
    }

    protected function writeData()
    {
        $source = $this->stream->expandPrototypeAttribute('source');

        $file = base_path(trim($source->get('file', 'streams/' . $this->stream->handle . '.json'), '/\\'));

        $keyName = $this->stream->config('key_name', 'id');

        $contents = json_decode(file_get_contents($file), true);

        $contents['data'] = $this->data;

        file_put_contents($file, $contents);

        return true;
    }
}
