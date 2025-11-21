<?= $this->extend('layouts/member') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="page-title">Edit Post</h1>
            <p class="text-muted">Edit balasan Anda di forum diskusi</p>
        </div>
        <div class="col-auto">
            <a href="<?= base_url('member/forum/thread/' . ($post->thread_id ?? '')) ?>" class="btn btn-secondary">
                <i class="material-icons-outlined">arrow_back</i> Kembali ke Thread
            </a>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <?php if (isset($post) && $post): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="material-icons-outlined">edit</i>
                        Edit Post Anda
                    </h5>
                </div>
                <div class="card-body">
                    <form action="<?= base_url('member/forum/post/update/' . $post->id) ?>" method="POST" id="editPostForm">
                        <?= csrf_field() ?>

                        <!-- Original Post Info -->
                        <div class="alert alert-info">
                            <strong>Thread:</strong> <?= esc($post->thread_title ?? 'Thread') ?><br>
                            <strong>Diposting:</strong> <?= date('d F Y, H:i', strtotime($post->created_at)) ?>
                            <?php if ($post->updated_at && $post->updated_at != $post->created_at): ?>
                                <br><strong>Terakhir diedit:</strong> <?= date('d F Y, H:i', strtotime($post->updated_at)) ?>
                            <?php endif; ?>
                        </div>

                        <!-- Post Content -->
                        <div class="mb-3">
                            <label for="content" class="form-label">
                                Konten Post <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control <?= session('errors.content') ? 'is-invalid' : '' ?>"
                                      id="content"
                                      name="content"
                                      rows="10"
                                      required
                                      minlength="10"
                                      maxlength="5000"><?= esc($post->content) ?></textarea>
                            <?php if (session('errors.content')): ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.content') ?>
                                </div>
                            <?php endif; ?>
                            <div class="form-text">
                                <span id="charCount"><?= strlen($post->content) ?></span> / 5000 karakter
                            </div>
                        </div>

                        <!-- Edit Reason -->
                        <div class="mb-3">
                            <label for="edit_reason" class="form-label">Alasan Edit (Opsional)</label>
                            <input type="text"
                                   class="form-control"
                                   id="edit_reason"
                                   name="edit_reason"
                                   placeholder="Contoh: Perbaiki typo, tambah informasi, dll"
                                   maxlength="200">
                            <small class="text-muted">Alasan edit akan ditampilkan di post</small>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="material-icons-outlined">save</i>
                                Simpan Perubahan
                            </button>
                            <a href="<?= base_url('member/forum/thread/' . $post->thread_id) ?>" class="btn btn-outline-secondary">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Preview Card -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="material-icons-outlined">visibility</i>
                        Preview
                    </h5>
                </div>
                <div class="card-body">
                    <div id="contentPreview" class="border rounded p-3 bg-light">
                        <?= nl2br(esc($post->content)) ?>
                    </div>
                </div>
            </div>

            <!-- Forum Rules Reminder -->
            <div class="alert alert-warning mt-3">
                <h6 class="alert-heading">
                    <i class="material-icons-outlined">rule</i>
                    Aturan Forum
                </h6>
                <ul class="mb-0 small">
                    <li>Gunakan bahasa yang sopan dan profesional</li>
                    <li>Jangan spam atau posting konten yang tidak relevan</li>
                    <li>Hormati pendapat anggota lain</li>
                    <li>Tidak diperkenankan posting konten SARA, pornografi, atau kekerasan</li>
                    <li>Post yang melanggar aturan akan dihapus oleh moderator</li>
                </ul>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="material-icons-outlined text-danger" style="font-size: 64px;">error</i>
                    <h4 class="mt-3">Post Tidak Ditemukan</h4>
                    <p class="text-muted">Post yang ingin Anda edit tidak ditemukan atau sudah dihapus.</p>
                    <a href="<?= base_url('member/forum') ?>" class="btn btn-primary mt-2">
                        Kembali ke Forum
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Character counter
    document.getElementById('content').addEventListener('input', function() {
        const charCount = this.value.length;
        document.getElementById('charCount').textContent = charCount;

        // Update preview
        updatePreview();
    });

    // Live preview
    function updatePreview() {
        const content = document.getElementById('content').value;
        const preview = document.getElementById('contentPreview');

        // Convert newlines to <br> for preview
        preview.innerHTML = content.replace(/\n/g, '<br>');
    }

    // Form validation
    document.getElementById('editPostForm').addEventListener('submit', function(e) {
        const content = document.getElementById('content').value.trim();

        if (content.length < 10) {
            e.preventDefault();
            alert('Konten post minimal 10 karakter!');
            return false;
        }

        if (content.length > 5000) {
            e.preventDefault();
            alert('Konten post maksimal 5000 karakter!');
            return false;
        }

        // Confirm submission
        if (!confirm('Simpan perubahan post ini?')) {
            e.preventDefault();
            return false;
        }
    });

    // Initialize preview on load
    updatePreview();
</script>
<?= $this->endSection() ?>
