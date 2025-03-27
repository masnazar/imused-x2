          
        <!-- Scroll To Top -->
        <div class="scrollToTop">
           <span class="arrow"><i class="ti ti-arrow-narrow-up fs-20"></i></span>
        </div>
        <div id="responsive-overlay"></div>
        <!-- Scroll To Top -->

         <!-- Tambahkan ini sebelum script lain -->
         <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
         <script>
            console.log('🟢 jQuery Loaded:', typeof jQuery);
         </script>

         <!-- ApexChart CDN -->
         <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

         <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>



        <!-- Include SweetAlert -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>
        <script src="https://cdn.datatables.net/responsive/2.3.0/js/dataTables.responsive.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.6/pdfmake.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

        <!-- Popper JS -->
        <script src="<?php echo base_url('assets/libs/@popperjs/core/umd/popper.min.js'); ?>"></script>

        <!-- Bootstrap JS -->
        <script src="<?php echo base_url('assets/libs/bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>

        <!-- Defaultmenu JS -->
        <script src="<?php echo base_url('assets/js/defaultmenu.min.js'); ?>"></script>

        <!-- Node Waves JS-->
        <script src="<?php echo base_url('assets/libs/node-waves/waves.min.js'); ?>"></script>

        <!-- Sticky JS -->
        <script src="<?php echo base_url('assets/js/sticky.js'); ?>"></script>

        <!-- Simplebar JS -->
        <script src="<?php echo base_url('assets/libs/simplebar/simplebar.min.js'); ?>"></script>
        <script src="<?php echo base_url('assets/js/simplebar.js'); ?>"></script>

        <!-- Auto Complete JS -->
        <script src="<?php echo base_url('assets/libs/@tarekraafat/autocomplete.js/autoComplete.min.js'); ?>"></script>

        <!-- Color Picker JS -->
        <script src="<?php echo base_url('assets/libs/@simonwep/pickr/pickr.es5.min.js'); ?>"></script>

        <!-- Date & Time Picker JS -->
        <script src="<?php echo base_url('assets/libs/flatpickr/flatpickr.min.js'); ?>"></script>

        <?= $this->renderSection('scripts'); ?>
        
        <!-- Custom-Switcher JS -->
        <script src="<?php echo base_url('assets/js/custom-switcher.min.js'); ?>"></script>

        <!-- Custom JS -->
        <script src="<?php echo base_url('assets/js/custom.js'); ?>"></script>
     
        <?= $this->renderSection('script') ?>
