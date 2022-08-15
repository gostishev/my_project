<?php

namespace App\Helper;

use Symfony\Component\HttpFoundation\Request;

class TransformJsonBody
{
    public function transformJsonBody(Request $request)
    {
        $data = json_decode($request->getContent(), true, 128, \JSON_THROW_ON_ERROR);

        if ($data === null) {
            return $request;
        }

        $request->request->replace($data);
        return $request;
    }

}
