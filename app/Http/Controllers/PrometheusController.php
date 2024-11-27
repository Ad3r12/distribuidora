<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;
use Prometheus\Storage\InMemory;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PrometheusController extends Controller
{
    public function metrics()
    {
        try {
            // Configura el almacenamiento en memoria
            $adapter = new InMemory();
            $registry = new CollectorRegistry($adapter);

            // Métrica 1: Contador de solicitudes (requests_total)
            $requestCounter = $registry->registerCounter(
                'distribuidora', // Namespace: distribuidora
                'requests_total', 
                'Total de solicitudes recibidas',
                ['method', 'status']
            );
            $requestCounter->inc(['GET', '200']); // Incrementa para una solicitud exitosa

            // Métrica 2: Histogram de tiempos de respuesta (response_duration_seconds)
            $responseTimeHistogram = $registry->registerHistogram(
                'distribuidora', // Namespace: distribuidora
                'response_duration_seconds',
                'Duración de las solicitudes en segundos',
                ['method'],
                [0.1, 0.5, 1, 2, 5] // Las categorías del histograma (rangos de tiempo)
            );
            $responseTimeHistogram->observe(0.2, ['GET']); // Simula un tiempo de respuesta

            // Métrica 3: Contador de errores (errors_total)
            $errorCounter = $registry->registerCounter(
                'distribuidora', // Namespace: distribuidora
                'errors_total',
                'Total de errores en las solicitudes',
                ['method', 'status']
            );
            $errorCounter->inc(['GET', '500']); // Simula un error interno del servidor

            // Métrica 4: Uso de memoria (memory_usage_bytes)
            $memoryUsageGauge = $registry->registerGauge(
                'distribuidora', // Namespace: distribuidora
                'memory_usage_bytes',
                'Uso de memoria en bytes',
            );
            $memoryUsageGauge->set(memory_get_usage()); // Establece el uso de memoria actual

            $activeConnectionsGauge = $registry->registerGauge(
                'distribuidora', // Namespace: distribuidora
                'active_connections',
                'Número de conexiones activas a la base de datos',
            );
            $activeConnectionsGauge->set(DB::select('SHOW STATUS WHERE `variable_name` = "Threads_connected"')[0]->Value); // Ejemplo de obtener conexiones activas de MySQL
            
            // Métrica 5: Número de conexiones activas a la base de datos (active_connections)
            // Aquí comentamos la línea que está causando problemas
            //$activeConnectionsGauge = $registry->registerGauge(
            //    'distribuidora', // Namespace: distribuidora
            //    'active_connections',
            //    'Número de conexiones activas a la base de datos',
            //);
            //$activeConnectionsGauge->set(DB::connection()->getPdo()->getAttribute(\PDO::ATTR_CONNECTION_STATUS)); // Esta línea está fallando

            // Renderiza las métricas en el formato adecuado para Prometheus
            $renderer = new RenderTextFormat();
            $result = $renderer->render($registry->getMetricFamilySamples());

            // Devuelve las métricas con el encabezado adecuado
            return response($result)->header('Content-Type', RenderTextFormat::MIME_TYPE);
        } catch (Exception $e) {
            // Manejo de errores
            Log::error('Error al obtener métricas: ' . $e->getMessage());
            return response()->json(['error' => 'No se pueden obtener las métricas', 'message' => $e->getMessage()], 500);
        }
    }
}
