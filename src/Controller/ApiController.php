<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    #[Route('/api/car-data', name: 'app_api_car_data')]
    public function getCarData(): JsonResponse
    {
        // Fetch car data from the external JSON file
        $carDataUrl = 'https://raw.githubusercontent.com/getFrontend/json-car-list/refs/heads/main/car-list.json';
        
        try {
            $jsonData = file_get_contents($carDataUrl);
            if ($jsonData === false) {
                throw new \Exception('Failed to fetch car data');
            }
            
            $carData = json_decode($jsonData, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON data: ' . json_last_error_msg());
            }
            
            return new JsonResponse($carData);
        } catch (\Exception $e) {
            // Fallback data in case external API is unavailable
            $fallbackData = [
                ['brand' => 'Audi', 'models' => ['A1', 'A3', 'A4', 'A5', 'A6', 'A7', 'A8', 'Q3', 'Q5', 'Q7', 'Q8', 'TT', 'R8']],
                ['brand' => 'BMW', 'models' => ['1 Series', '2 Series', '3 Series', '4 Series', '5 Series', '6 Series', '7 Series', 'X1', 'X2', 'X3', 'X4', 'X5', 'X6', 'X7', 'Z4', 'i3', 'i8']],
                ['brand' => 'Mercedes-Benz', 'models' => ['A-Class', 'B-Class', 'C-Class', 'CLA', 'CLS', 'E-Class', 'G-Class', 'GLA', 'GLB', 'GLC', 'GLE', 'GLS', 'S-Class', 'SL', 'SLC', 'V-Class']],
                ['brand' => 'Volkswagen', 'models' => ['Golf', 'Polo', 'Passat', 'Tiguan', 'Touareg', 'Arteon', 'ID.3', 'ID.4', 'ID.5', 'T-Cross', 'T-Roc', 'Touran', 'Sharan']],
                ['brand' => 'Toyota', 'models' => ['Yaris', 'Corolla', 'Camry', 'Prius', 'RAV4', 'Highlander', 'Land Cruiser', 'Hilux', 'Auris', 'Avensis', 'Aygo', 'C-HR', 'Supra']],
                ['brand' => 'Honda', 'models' => ['Civic', 'Accord', 'CR-V', 'HR-V', 'Pilot', 'Ridgeline', 'Insight', 'Fit', 'Odyssey', 'Passport']],
                ['brand' => 'Ford', 'models' => ['Fiesta', 'Focus', 'Mondeo', 'Mustang', 'Kuga', 'Explorer', 'Edge', 'F-150', 'Ranger', 'Transit', 'Galaxy', 'S-Max']],
                ['brand' => 'Nissan', 'models' => ['Micra', 'Sentra', 'Altima', 'Maxima', 'Juke', 'Qashqai', 'X-Trail', 'Pathfinder', 'Murano', '370Z', 'GT-R', 'Leaf']],
                ['brand' => 'Hyundai', 'models' => ['i10', 'i20', 'i30', 'Elantra', 'Sonata', 'Tucson', 'Santa Fe', 'Kona', 'Ioniq', 'Nexo', 'Genesis']],
                ['brand' => 'Kia', 'models' => ['Picanto', 'Rio', 'Ceed', 'Optima', 'Sportage', 'Sorento', 'Stonic', 'Niro', 'Soul', 'Stinger']],
                ['brand' => 'Mazda', 'models' => ['Mazda2', 'Mazda3', 'Mazda6', 'CX-3', 'CX-5', 'CX-9', 'MX-5', 'MX-30']],
                ['brand' => 'Subaru', 'models' => ['Impreza', 'Legacy', 'Outback', 'Forester', 'XV', 'WRX', 'BRZ', 'Ascent']],
                ['brand' => 'Lexus', 'models' => ['IS', 'ES', 'GS', 'LS', 'NX', 'RX', 'GX', 'LX', 'LC', 'RC', 'UX']],
                ['brand' => 'Infiniti', 'models' => ['Q30', 'Q50', 'Q60', 'QX30', 'QX50', 'QX60', 'QX70', 'QX80']],
                ['brand' => 'Acura', 'models' => ['ILX', 'TLX', 'RLX', 'RDX', 'MDX', 'NSX']],
                ['brand' => 'Volvo', 'models' => ['V40', 'S60', 'S90', 'V60', 'V90', 'XC40', 'XC60', 'XC90']],
                ['brand' => 'Jaguar', 'models' => ['XE', 'XF', 'XJ', 'F-PACE', 'E-PACE', 'I-PACE', 'F-TYPE']],
                ['brand' => 'Land Rover', 'models' => ['Range Rover Evoque', 'Range Rover Velar', 'Range Rover Sport', 'Range Rover', 'Discovery', 'Discovery Sport', 'Defender']],
                ['brand' => 'Porsche', 'models' => ['718 Boxster', '718 Cayman', '911', 'Panamera', 'Macan', 'Cayenne', 'Taycan']],
                ['brand' => 'Tesla', 'models' => ['Model S', 'Model 3', 'Model X', 'Model Y', 'Roadster', 'Cybertruck']]
            ];
            
            return new JsonResponse($fallbackData);
        }
    }
}
