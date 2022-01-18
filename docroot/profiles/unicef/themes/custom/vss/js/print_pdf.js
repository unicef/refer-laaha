(function ($, Drupal, drupalSettings) {
    'use strict';
        $(".export-to-pdf").click(function(){
            generatePDF();
        })
        function generatePDF() {
            // Choose the element that our invoice is rendered in.
            const element = document.getElementById('pdf-content');
            var opt = {
                margin:       1,
                filename:     'myfile.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2 },
                jsPDF:        { unit: 'in', format: 'letter', orientation: 'landscape' }
              };
              
              // New Promise-based usage:
              html2pdf(element,opt);
        }
  })(jQuery, Drupal, drupalSettings);