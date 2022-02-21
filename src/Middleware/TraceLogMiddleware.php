<?php

namespace Forecho\LaravelTraceLog\Middleware;

use Closure;
use Forecho\LaravelTraceLog\TraceLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TraceLogMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $this->setTraceId($request);
        try {
            $ignoredHeaderKeys = explode(',', config('tracelog.filter_ignored_header_keys', 'authorization'));
            $ignoredKeys = explode(',', config('tracelog.filter_ignored_keys', ''));
            $hideKeys = explode(',', config('tracelog.filter_hide_keys', ''));
            $halfHideKeys = explode(',', config('tracelog.filter_half_hide_keys', ''));

            $requestParams = $this->paramsFilter($request->all(), $ignoredKeys, $hideKeys, $halfHideKeys);
            $requestHeaderParams = $this->headerFilter($request->headers->all(), $ignoredHeaderKeys);
        } catch (\Exception $e) {
            TraceLog::error('request_params_filter_key_config_error', [
                'log_filter_ignored_header_keys' => config('tracelog.filter_ignored_header_keys', ''),
                'log_filter_ignored_keys' => config('tracelog.filter_ignored_keys', ''),
                'log_filter_hide_keys' => config('tracelog.filter_hide_keys', ''),
                'log_filter_half_hide_keys' => config('tracelog.filter_half_hide_keys', ''),
                'exception' => (string)$e,
            ]);
            $requestParams = $request->all();
            $requestHeaderParams = $request->headers->all();
        }

        $requestInfo = [
            'path' => sprintf('%s:%s', $request->method(), $request->path()),
            'params' => $requestParams,
            'header' => $requestHeaderParams,
        ];

        $beginMillisecond = round(microtime(true) * 1000);

        $context = [
            'beginMillisecond' => $beginMillisecond,
        ];

        TraceLog::info('request_received', $context, $requestInfo);

        /** @var Response $response */
        $response = $next($request);

        // after
        $statusCode = $response->getStatusCode();
        $responseInfo = [
            'status_code' => $statusCode,
            'body' => json_decode($response->content(), true),
        ];
        $endMillisecond = round(microtime(true) * 1000);
        $context = [
            'endMillisecond' => $endMillisecond,
            'spendingMillisecond' => $endMillisecond - $beginMillisecond,
        ];

        if ($statusCode >= 400) {
            TraceLog::error('request_responded_error', $context, $requestInfo, $responseInfo);
        } else {
            TraceLog::info('request_responded_success', $context, $requestInfo, $responseInfo);
        }

        return $response;
    }

    protected function setTraceId(Request $request)
    {
        $key = config('tracelog.trace_id_header_key');
        if ($request->hasHeader($key)) {
            $traceId = $request->header($key);
            config(['tracelog.trace_id' => $traceId]);
        }
    }

    protected function headerFilter(array $params, array $ignoredHeaderKeys): array
    {
        foreach ($params as $key => &$ignored) {
            if ($key && in_array($key, $ignoredHeaderKeys)) {
                unset($params[$key]);
            }
        }

        return $params;
    }

    protected function paramsFilter(array $params, array $ignoredKeys, array $hideKeys, array $halfHideKeys): array
    {
        if (!$hideKeys && !$halfHideKeys && !$ignoredKeys) {
            return $params;
        }
        foreach ($params as $key => &$item) {
            if (is_array($item)) {
                $item = $this->paramsFilter($item, $ignoredKeys, $hideKeys, $halfHideKeys);

                continue;
            }
            if ($key && in_array($key, $ignoredKeys)) {
                unset($params[$key]);
            } elseif ($key && in_array($key, $hideKeys)) {
                $item = $this->paramReplace($item);
            } elseif ($key && in_array($key, $halfHideKeys)) {
                $item = $this->paramPartialHiddenReplace($item);
            }
        }

        return $params;
    }

    protected function paramReplace(string $value): string
    {
        return str_repeat('*', strlen($value));
    }


    protected function paramPartialHiddenReplace(string $value): string
    {
        $valueLength = strlen($value);
        if ($valueLength > 2) {
            $showLength = ceil($valueLength * 0.2);
            $hideLength = $valueLength - $showLength * 2;
            $newValue = mb_substr($value, 0, $showLength)
                .str_repeat('*', $hideLength)
                .mb_substr($value, -1 * $showLength);
        } else {
            $newValue = $this->paramReplace($value);
        }

        return $newValue;
    }
}
