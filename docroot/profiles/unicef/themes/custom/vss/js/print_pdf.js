(function ($, Drupal, drupalSettings) {
    'use strict';
  
    $(document).ready(function(){
        console.log('fkdksdfgmk');
        function generatePDF() {
            // Choose the element that our invoice is rendered in.
            const element = document.getElementById('pdf-print-nodes');
            alert(element);
            // Choose the element and save the PDF for our user.
            var opt = {
                margin:       1,
                filename:     'myfile.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2 },
                jsPDF:        { unit: 'in', format: 'letter', orientation: 'landsacpe' }
                };
            html2pdf().from(element).save();
        }
    });
  
  
  
  })(jQuery, Drupal, drupalSettings);