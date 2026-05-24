<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Include DomPDF autoloader
require_once APPPATH . '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

class Pdf {
    
    private $dompdf;
    private $html;
    private $paper;
    private $orientation;
    
    public function __construct() {
        // Initialize DomPDF with options
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('isJavascriptEnabled', false);
        $options->set('defaultPaperSize', 'A4');
        $options->set('defaultPaperOrientation', 'portrait');
        
        $this->dompdf = new Dompdf($options);
        $this->paper = 'A4';
        $this->orientation = 'portrait';
    }
    
    public function loadHtml($html) {
        $this->html = $html;
    }
    
    public function setPaper($paper, $orientation = 'portrait') {
        $this->paper = $paper;
        $this->orientation = $orientation;
    }
    
    public function render() {
        // Clean the HTML content before rendering
        $html_content = $this->html;
        
        // Remove any interactive elements that shouldn't be in PDF
        $html_content = preg_replace('/<script[^>]*>.*?<\/script>/s', '', $html_content);
        $html_content = preg_replace('/<button[^>]*>.*?<\/button>/s', '', $html_content);
        $html_content = preg_replace('/<input[^>]*>/s', '', $html_content);
        $html_content = preg_replace('/<select[^>]*>.*?<\/select>/s', '', $html_content);
        $html_content = preg_replace('/<form[^>]*>.*?<\/form>/s', '', $html_content);
        
        // Remove Bootstrap classes that might interfere with PDF rendering
        $html_content = preg_replace('/class="[^"]*btn[^"]*"/', '', $html_content);
        $html_content = preg_replace('/class="[^"]*form-control[^"]*"/', '', $html_content);
        $html_content = preg_replace('/class="[^"]*d-flex[^"]*"/', '', $html_content);
        
        // Load the cleaned HTML into DomPDF
        $this->dompdf->loadHtml($html_content);
        
        // Set paper size and orientation
        $this->dompdf->setPaper($this->paper, $this->orientation);
        
        // Render the HTML to PDF
        $this->dompdf->render();
    }
    
    public function stream($filename) {
        // Ensure the PDF is rendered
        if (!$this->dompdf->getCanvas()) {
            $this->render();
        }
        
        // Output the PDF
        $this->dompdf->stream($filename, [
            'Attachment' => 1, // Force download
            'compress' => true
        ]);
    }
    
    public function output() {
        // Ensure the PDF is rendered
        if (!$this->dompdf->getCanvas()) {
            $this->render();
        }
        
        // Return the PDF as string
        return $this->dompdf->output();
    }
}
