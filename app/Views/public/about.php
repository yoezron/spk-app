<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>

<!-- Hero Section -->
<div class="bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 mb-3">Tentang Serikat Pekerja Kampus</h1>
                <p class="lead">Organisasi yang memperjuangkan hak dan kesejahteraan pekerja kampus di seluruh Indonesia</p>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="container py-5">
    <!-- Vision & Mission -->
    <div class="row mb-5">
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h3 class="card-title">
                        <i class="material-icons-outlined text-primary">visibility</i>
                        Visi
                    </h3>
                    <p class="card-text">
                        Menjadi organisasi serikat pekerja kampus yang profesional, demokratis, dan berdaya guna
                        dalam memperjuangkan hak dan kesejahteraan anggota serta memberikan kontribusi positif
                        terhadap dunia pendidikan Indonesia.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h3 class="card-title">
                        <i class="material-icons-outlined text-primary">track_changes</i>
                        Misi
                    </h3>
                    <ul class="card-text">
                        <li>Memperjuangkan hak dan kesejahteraan anggota</li>
                        <li>Meningkatkan kualitas SDM pekerja kampus</li>
                        <li>Membangun solidaritas antar pekerja</li>
                        <li>Memberikan advokasi dan pendampingan hukum</li>
                        <li>Berkontribusi pada kemajuan pendidikan nasional</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- History -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="mb-4">
                <i class="material-icons-outlined">history_edu</i>
                Sejarah
            </h2>
            <p>
                Serikat Pekerja Kampus didirikan dengan tujuan untuk menyatukan dan memperjuangkan
                hak-hak pekerja di lingkungan kampus di seluruh Indonesia. Berawal dari kesadaran
                akan pentingnya organisasi yang dapat mewadahi aspirasi dan melindungi kepentingan
                pekerja kampus, SPK terus berkembang menjadi organisasi yang solid dan terpercaya.
            </p>
            <p>
                Dengan anggota yang tersebar di berbagai universitas di Indonesia, SPK berkomitmen
                untuk terus memperjuangkan kesejahteraan anggota melalui advokasi, pendidikan, dan
                pemberdayaan.
            </p>
        </div>
    </div>

    <!-- Values -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="mb-4">
                <i class="material-icons-outlined">stars</i>
                Nilai-Nilai Kami
            </h2>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="material-icons-outlined text-primary" style="font-size: 48px;">groups</i>
                    <h5 class="card-title mt-3">Solidaritas</h5>
                    <p class="card-text">Membangun persatuan dan kebersamaan di antara seluruh anggota</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="material-icons-outlined text-primary" style="font-size: 48px;">gavel</i>
                    <h5 class="card-title mt-3">Keadilan</h5>
                    <p class="card-text">Memperjuangkan keadilan dan hak-hak pekerja secara konsisten</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="material-icons-outlined text-primary" style="font-size: 48px;">verified</i>
                    <h5 class="card-title mt-3">Integritas</h5>
                    <p class="card-text">Menjunjung tinggi kejujuran dan transparansi dalam setiap tindakan</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact CTA -->
    <div class="row">
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-body text-center py-5">
                    <h3 class="mb-3">Bergabung Bersama Kami</h3>
                    <p class="lead mb-4">
                        Mari bersama-sama memperjuangkan hak dan kesejahteraan pekerja kampus di Indonesia
                    </p>
                    <div class="btn-group" role="group">
                        <a href="<?= base_url('register') ?>" class="btn btn-primary btn-lg">
                            <i class="material-icons-outlined">person_add</i>
                            Daftar Sekarang
                        </a>
                        <a href="<?= base_url('contact') ?>" class="btn btn-outline-primary btn-lg">
                            <i class="material-icons-outlined">mail</i>
                            Hubungi Kami
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
