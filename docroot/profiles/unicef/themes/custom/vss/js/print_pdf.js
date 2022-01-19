(function ($, Drupal, drupalSettings) {
    'use strict';
        $(".export-to-pdf").on('click',function(){
            generatePDF();
        })
        function generatePDF() {
            // Choose the element that our invoice is rendered in.
            const element = document.getElementById('pdf-print-nodes');
            var opt = {
                margin:       1,
                filename:     'myfile.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2 },
                jsPDF:        { unit: 'in', format: 'letter', orientation: 'landscape' }
              };

              // New Promise-based usage:
              html2pdf().set(opt).from(element).save();
        }
  })(jQuery, Drupal, drupalSettings);