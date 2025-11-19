<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ConvertPersianNumbers
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Convert Persian/Farsi numbers to English numbers in request data
        $data = $request->all();
        $this->convertNumbersInArray($data);
        
        // Replace the request data with converted data
        $request->replace($data);
        
        return $next($request);
    }

    /**
     * Recursively convert Persian numbers to English numbers in array data
     *
     * @param array $data
     * @return void
     */
    private function convertNumbersInArray(array &$data): void
    {
        foreach ($data as $key => &$value) {
            if (is_array($value)) {
                $this->convertNumbersInArray($value);
            } elseif (is_string($value)) {
                $value = $this->convertPersianNumbers($value);
            }
        }
    }

    /**
     * Convert Persian/Farsi numbers to English numbers in a string
     *
     * @param string $text
     * @return string
     */
    private function convertPersianNumbers(string $text): string
    {
        // Persian/Farsi numbers to English numbers mapping
        $persianNumbers = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $englishNumbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        
        // Arabic-Indic numbers to English numbers mapping
        $arabicNumbers = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        
        // Convert Persian numbers
        $text = str_replace($persianNumbers, $englishNumbers, $text);
        
        // Convert Arabic-Indic numbers
        $text = str_replace($arabicNumbers, $englishNumbers, $text);
        
        return $text;
    }
}
