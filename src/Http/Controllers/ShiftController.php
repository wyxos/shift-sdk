<?php

namespace Wyxos\Shift\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\App;

class ShiftController extends Controller
{
    /**
     * Display the shift dashboard.
     *
     * @return string|\Illuminate\Http\Response
     */
    public function index()
    {
        // In local development, proxy to the Vite dev server if it's running
        if (App::environment('local') && $this->isViteDevServerRunning()) {
            try {
                $response = Http::get($this->getViteDevServerUrl());

                if ($response->successful()) {
                    return response($response->body(), 200)
                        ->header('Content-Type', 'text/html');
                }
            } catch (\Exception $e) {
                // If there's an error connecting to the Vite dev server, fall back to the built files
            }
        }

        // In production or if Vite dev server is not running, serve the built files
        return file_get_contents(public_path('/shift/index.html'));
    }

    /**
     * Check if the Vite dev server is running.
     *
     * @return bool
     */
    private function isViteDevServerRunning()
    {
        try {
            $response = Http::timeout(1)->head($this->getViteDevServerUrl());
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get the URL of the Vite dev server.
     *
     * @return string
     */
    private function getViteDevServerUrl()
    {
        $host = config('app.domain', 'shift-sdk-package.test');
        $port = 5173; // Default Vite dev server port

        return "https://{$host}:{$port}/shift/";
    }
}
