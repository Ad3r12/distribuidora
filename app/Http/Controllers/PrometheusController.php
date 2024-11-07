<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;
use Prometheus\Storage\InMemory;
use Exception;

class PrometheusController extends Controller
{
    public function metrics()
    {
        try {
            // Configura el almacenamiento en memoria
            $adapter = new InMemory();
            $registry = new CollectorRegistry($adapter);

            // Registra el contador de métricas
            $counter = $registry->registerCounter('your_namespace', 'requests_total', 'Total requests', ['method', 'status']);
            $counter->inc(['GET', '200']); // Incrementa la métrica para pruebas

            // Renderiza las métricas en el formato correcto
            $renderer = new RenderTextFormat();
            $result = $renderer->render($registry->getMetricFamilySamples());

            // Retorna las métricas con el encabezado correcto
            return response($result)->header('Content-Type', RenderTextFormat::MIME_TYPE);
        } catch (Exception $e) {
            // Manejo de errores
            return response()->json(['error' => 'Unable to fetch metrics', 'message' => $e->getMessage()], 500);
        }
    }
}
