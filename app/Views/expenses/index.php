<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?>Daftar Expenses<?= $this->endSection() ?>

<?= $this->section('content') ?>
  
  <!-- Breadcrumbs & Header -->
  <div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2 mb-3">
    <div>
      <nav>
        <ol class="breadcrumb mb-1">
          <li class="breadcrumb-item">
            <a href="<?= base_url() ?>"><i class="fas fa-home"></i> Dashboard</a>
          </li>
          <li class="breadcrumb-item active">Daftar Expenses</li>
        </ol>
      </nav>
      <h1 class="page-title fw-medium fs-18 mb-0">
        <i class="ti ti-file-text"></i> Daftar Expenses
      </h1>
    </div>
  </div>

  <?= $this->include('layouts/components/flashMessage') ?>

  <!-- Filter Periode -->
  <div class="mb-4">
    <?= $date_filter /* kirim dari controller */ ?>
  </div>

  <!-- Statistik Ringkas -->
  <div class="row mb-4">
    <!-- Total Transaksi Pengeluaran -->
    <div class="col-xl-4 mb-3">
      <div class="card custom-card border-primary border-opacity-50 main-content-card">
        <div class="card-body d-flex align-items-center justify-content-between">
          <span class="badge bg-secondary-transparent text-secondary fw-semibold">
            <i class="fe fe-file-text"></i>
          </span>
          <div class="text-end">
            <div class="d-flex align-items-center justify-content-end">
              <h4 id="stat_count" class="fw-medium mb-0 me-2">0</h4>
              <span id="pct_count" class="badge ms-2" style="display:none;"></span>
            </div>
            <p class="mb-0 fs-12 fw-medium">TOTAL TRANSAKSI PENGELUARAN</p>
          </div>
        </div>
      </div>
    </div>
    <!-- Total Pengeluaran -->
    <div class="col-xl-4 mb-3">
      <div class="card custom-card border-primary1 border-opacity-50 main-content-card">
        <div class="card-body d-flex align-items-center justify-content-between">
          <span class="badge bg-secondary-transparent text-secondary fw-semibold">
            <i class="fe fe-arrow-down-circle"></i>
          </span>
          <div class="text-end">
            <div class="d-flex align-items-center justify-content-end">
              <h4 id="stat_total" class="fw-medium mb-0 me-2">Rp 0</h4>
              <span id="pct_total" class="badge ms-2" style="display:none;"></span>
            </div>
            <p class="mb-0 fs-12 fw-medium">TOTAL PENGELUARAN</p>
          </div>
        </div>
      </div>
    </div>
    <!-- Rata-rata Pengeluaran -->
    <div class="col-xl-4 mb-3">
      <div class="card custom-card border-primary2 border-opacity-50 main-content-card">
        <div class="card-body d-flex align-items-center justify-content-between">
          <span class="badge bg-secondary-transparent text-secondary fw-semibold">
            <i class="fe fe-percent"></i>
          </span>
          <div class="text-end">
            <div class="d-flex align-items-center justify-content-end">
              <h4 id="stat_avg" class="fw-medium mb-0 me-2">Rp 0</h4>
              <span id="pct_avg" class="badge ms-2" style="display:none;"></span>
            </div>
            <p class="mb-0 fs-12 fw-medium">RATA-RATA PENGELUARAN</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Tombol Analisis & Insight -->
  <button id="btn-ai-expenses" class="btn btn-info mb-3">
    <span id="aiSpinner" class="spinner-border spinner-border-sm me-1" style="display:none;"></span>
    <i class="fe fe-activity"></i> Analisis & Insight
  </button>

  <!-- Modal Insight -->
  <div class="modal fade" id="insightModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Insight & Rekomendasi</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <!-- akan di‐inject via JS -->
        </div>
      </div>
    </div>
  </div>

  <!-- Tabel Expenses -->
  <div class="card custom-card">
    <div class="card-header d-flex justify-content-between">
      <h5 class="card-title">Daftar Expenses</h5>
      <a href="<?= site_url('expenses/create') ?>" class="btn btn-primary btn-wave">
        <i class="ri-add-line me-1"></i> Tambah Expense
      </a>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table id="expensesTable" class="table text-nowrap table-bordered">
          <thead>
            <tr>
              <th>No</th>
              <th>Tanggal</th>
              <th>Deskripsi</th>
              <th>COA</th>
              <th>Brand</th>
              <th>Sumber Pengeluaran</th>
              <th class="text-end">Jumlah</th>
              <th>Tipe Proses</th>
              <th>Diproses Oleh</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
<?= $this->endSection() ?>


<?= $this->section('scripts') ?>
<script>
// --- CSRF setup ---
const csrfName  = '<?= csrf_token() ?>';
let   csrfHash  = '<?= csrf_hash() ?>';
const csrfHeader = 'X-CSRF-TOKEN';

// tunggu dokumen siap
$(function(){
  const jenisFilter   = $('#jenisFilter'),
        periodeSelect = $('select[name="periode"]'),
        startDate     = $('input[name="start_date"]'),
        endDate       = $('input[name="end_date"]'),
        btnAnalyze    = $('#btn-ai-expenses'),
        spinner       = $('#aiSpinner'),
        insightModal  = new bootstrap.Modal($('#insightModal'));

  // 1) Inisialisasi DataTable
  const table = $('#expensesTable').DataTable({
    processing: true,
    serverSide: true,
    order: [[1,'desc']],
    ajax: {
      url: "<?= site_url('expenses/get-data') ?>",
      type: 'POST',
      data: d => {
        d[csrfName]    = csrfHash;
        d.jenis_filter = jenisFilter.val();
        d.periode      = periodeSelect.val();
        d.start_date   = startDate.val();
        d.end_date     = endDate.val();
      },
      dataSrc: json => {
        if (json[csrfName]) csrfHash = json[csrfName];
        return json.data || [];
      },
      complete: fetchStatistics
    },
    columns: [
      { data:null, orderable:false, className:'text-center',
        render: (_,__,___,m)=> m.row + m.settings._iDisplayStart +1 },
      { data:'date', render: formatTanggal },
      { data:'description' },
      { data:'coa_code', render:(c,_,r)=> `${c} – ${r.coa_name}` },
      { data:'brand_name', defaultContent:'-' },
      { data:'platform_name', defaultContent:'-' },
      { data:'amount', className:'text-end',
        render: $.fn.dataTable.render.number('.',',',2) },
      { data:'type' },
      { data:'processed_by' },
      { data:'id', orderable:false, className:'text-center',
        render: id => `
          <a href="<?= site_url('expenses/edit') ?>/${id}" class="btn btn-sm btn-warning me-1">Edit</a>
          <button class="btn btn-sm btn-danger btn-del" data-id="${id}">Del</button>`
      }
    ]
  });

  // 2) Reload on filter change
  jenisFilter.add(periodeSelect).add(startDate).add(endDate)
    .on('change', ()=> table.ajax.reload());

  // 3) Delete dengan SweetAlert2
  $('#expensesTable').on('click','.btn-del', function(){
    const id = $(this).data('id');
    Swal.fire({
      title: 'Yakin ingin menghapus expense ini?',
      text: 'Data akan dihapus secara permanen.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Ya, hapus!',
      cancelButtonText: 'Batal',
      reverseButtons: true
    }).then(result => {
      if (!result.isConfirmed) return;

      $.ajax({
        url: `<?= site_url('expenses/delete') ?>/${id}`,
        type: 'DELETE',
        headers: { [csrfHeader]: csrfHash },
        success: res => {
          if (res[csrfName]) csrfHash = res[csrfName];
          table.ajax.reload(null, false);
          Swal.fire({
            title: 'Terhapus!',
            text: 'Expense berhasil dihapus.',
            icon: 'success',
            timer: 1400,
            showConfirmButton: false
          });
        },
        error: (xhr,status,err) => {
          console.error('Hapus gagal:', status, err);
          Swal.fire('Gagal','Expense gagal dihapus. Coba lagi.','error');
        }
      });
    });
  });

  // 4) Helper periode
  function periodeText() {
    const teks = periodeSelect.find('option:selected').text().toLowerCase();
    return teks.includes('custom')
      ? `${startDate.val()} s.d. ${endDate.val()}`
      : periodeSelect.find('option:selected').text();
  }

  // 5) Fetch statistik
  function fetchStatistics() {
    $.ajax({
      url: "<?= site_url('expenses/get-statistics') ?>",
      type: 'POST',
      dataType: 'json',
      data: {
        [csrfName]   : csrfHash,
        jenis_filter : jenisFilter.val(),
        periode      : periodeSelect.val(),
        start_date   : startDate.val(),
        end_date     : endDate.val()
      },
      success: res => {
        if (res[csrfName]) csrfHash = res[csrfName];
        $('#stat_count').text(res.count||0);  renderBadge('#pct_count', res.pct_count);
        $('#stat_total').text(new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR'}).format(res.total||0));
        renderBadge('#pct_total', res.pct_total);
        $('#stat_avg').text(new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR'}).format(res.avg||0));
        renderBadge('#pct_avg', res.pct_avg);
      },
      error: xhr => console.error('Error statistik:', xhr)
    });
  }

  // 6) Render badge %
  function renderBadge(sel,pct) {
    const $el = $(sel);
    if (pct==null) {
      $el.show().attr('class','badge bg-info text-white fw-semibold ms-2').text('∞ %');
      return;
    }
    const up = pct>=0, icon=up?'fe fe-arrow-up':'fe fe-arrow-down',
          cls = up?'bg-success text-white':'bg-danger text-white';
    $el.show().attr('class',`badge ${cls} fw-semibold ms-2`)
       .html(`<i class="${icon}"></i> ${Math.abs(pct).toFixed(1)}%`);
  }

  // 7) Format tanggal
  function formatTanggal(d) {
    if (!d) return '-';
    const dt = new Date(d),
          hari = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'],
          bln  = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    return `${hari[dt.getDay()]}, ${String(dt.getDate()).padStart(2,'0')} ${bln[dt.getMonth()]} ${dt.getFullYear()}`;
  }

  // 8) Panggil AI Insight (tidak diubah)
  btnAnalyze.on('click', () => {
    spinner.show(); btnAnalyze.prop('disabled', true);
    $.ajax({
      url: "<?= site_url('expenses/analyze') ?>", type:'POST', dataType:'json',
      data: {
        [csrfName]: csrfHash,
        jenis_filter: jenisFilter.val(),
        periode: periodeSelect.val(),
        start_date: startDate.val(),
        end_date: endDate.val()
      }
    })
    .done(res => {
      if (res[csrfName]) csrfHash = res[csrfName];
      let ai;
      try { ai = JSON.parse(res.insight); }
      catch { ai={ringkasan:'',breakdown:{coa:[],platform:[]},risk_assessment:[],mitigation_plan:[],recommendations:[],konklusi:'',error:true}; }
      let html = `<h5 class="mb-3">Ringkasan Analisis Pengeluaran<br><small>Periode: ${periodeText()}</small></h5>`;
      if(ai.ringkasan) html+=`<p>${ai.ringkasan}</p>`;
      if(ai.breakdown.coa?.length){
        html+='<h6>🔢 Breakdown COA (Top 5)</h6><ul class="ps-3">';
        ai.breakdown.coa.forEach(i=>{const f=Number(i.total).toLocaleString('id-ID',{style:'currency',currency:'IDR'});html+=`<li>${i.coa_name}: ${f} (${i.percentage.toFixed(1)}%)</li>`;});
        html+='</ul>';
      }
      if(ai.breakdown.platform?.length){
        html+='<h6>🌐 Breakdown Sumber Pengeluaran (Top 5)</h6><ul class="ps-3">';
        ai.breakdown.platform.forEach(i=>{const f=Number(i.total).toLocaleString('id-ID',{style:'currency',currency:'IDR'});html+=`<li>${i.platform_name}: ${f} (${i.percentage.toFixed(1)}%)</li>`;});
        html+='</ul>';
      }
      if(ai.risk_assessment?.length){
        html+='<h6 class="mt-3">⚠️ Risk Assessment</h6><ul class="ps-3">';
        ai.risk_assessment.forEach(r=>html+=`<li>${r}</li>`);html+='</ul>';
      }
      if(ai.mitigation_plan?.length){
        html+='<h6 class="mt-3">🛡 Mitigation Plan</h6><ul class="ps-3">';
        ai.mitigation_plan.forEach(m=>html+=`<li>${m}</li>`);html+='</ul>';
      }
      if(ai.recommendations?.length){
        html+='<h6 class="mt-3">🛠 Recommendations</h6><ul class="ps-3">';
        ai.recommendations.forEach(r=>html+=`<li>${r}</li>`);html+='</ul>';
      }
      if(ai.konklusi) html+=`<h6 class="mt-3">✅ Konklusi</h6><p>${ai.konklusi}</p>`;
      if(ai.error) html+='<p class="text-danger">Gagal memproses insight AI.</p>';
      $('#insightModal .modal-body').html(html);
      insightModal.show();
    })
    .fail((_,s,e)=>{console.error('Error:',s,e);$('#insightModal .modal-body').html('<p class="text-danger">Gagal mengambil insight AI.</p>');insightModal.show();})
    .always(()=>{spinner.hide();btnAnalyze.prop('disabled', false);});
  });

});
</script>
<?= $this->endSection() ?>
