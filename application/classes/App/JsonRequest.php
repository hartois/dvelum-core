<?php
declare(strict_types=1);

namespace App;

use Dvelum\Filter;
use Dvelum\Request;

class JsonRequest {
    protected Request $request;
    protected string $body;
    protected array $bodyData;

    public function __construct(Request $request){
        $this->request = $request;
        $this->body = file_get_contents('php://input');
        try {
            $this->bodyData = json_decode($this->body, true);
        }catch (\Throwable $e){
            $this->bodyData = [];
        }
    }

    public function bodyParam(string $name, string $type, $default, ?string $root = null) {
        if($root) {
            if (!isset($this->bodyData[$root]) || !isset($this->bodyData[$root][$name]))
                return $default;

            return Filter::filterValue($type, isset($this->bodyData[$root][$name]));
        }

        if (!isset($this->bodyData[$name]))
            return $default;

        return Filter::filterValue($type, $this->bodyData[$name]);
    }
}
