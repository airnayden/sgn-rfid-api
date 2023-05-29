<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Collection;

class ResponseHelper
{
    /**
     * @param Collection|array $result
     * @return string
     */
    public static function formatResponseOk(Collection|array $result): string
    {
        if ($result instanceof Collection) {
            $result = $result->toArray();
        }

        return json_encode(['data' => $result]);
    }

    /**
     * @param \Throwable $e
     * @return string
     */
    public static function formatResponseError(\Throwable $e): string
    {
        return json_encode(['data' => null, 'error' => $e->getMessage()]);
    }
}