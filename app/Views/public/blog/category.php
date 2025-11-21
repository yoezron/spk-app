<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>

<!-- Hero Section -->
<div class="bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 mb-3">
                    <?php if (!empty($category)): ?>
                        <?= esc($category->name) ?>
                    <?php else: ?>
                        Kategori Blog
                    <?php endif; ?>
                </h1>
                <p class="lead">
                    <?php if (!empty($category->description)): ?>
                        <?= esc($category->description) ?>
                    <?php else: ?>
                        Artikel dan berita dari SPK
                    <?php endif; ?>
                </p>
            </div>
            <div class="col-lg-4 text-end">
                <a href="<?= base_url('blog') ?>" class="btn btn-light">
                    <i class="material-icons-outlined">arrow_back</i> Semua Artikel
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="container py-5">
    <div class="row">
        <!-- Main Articles -->
        <div class="col-lg-8">
            <!-- Category Info -->
            <?php if (!empty($category)): ?>
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1">
                                    <i class="material-icons-outlined">folder</i>
                                    <?= esc($category->name) ?>
                                </h5>
                                <p class="text-muted mb-0">
                                    <?= number_format($total_posts ?? 0) ?> artikel dalam kategori ini
                                </p>
                            </div>
                            <span class="badge bg-primary" style="font-size: 1rem;">
                                <?= number_format($total_posts ?? 0) ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Blog Posts -->
            <?php if (!empty($posts)): ?>
                <?php foreach ($posts as $post): ?>
                    <article class="card mb-4">
                        <?php if (!empty($post->featured_image)): ?>
                            <img src="<?= base_url('uploads/blog/' . $post->featured_image) ?>"
                                 class="card-img-top"
                                 alt="<?= esc($post->title) ?>"
                                 style="height: 250px; object-fit: cover;">
                        <?php endif; ?>

                        <div class="card-body">
                            <div class="mb-2">
                                <span class="badge bg-primary">
                                    <?= esc($post->category_name ?? $category->name ?? 'Umum') ?>
                                </span>
                                <?php if (!empty($post->tags)): ?>
                                    <?php
                                    $tags = is_array($post->tags) ? $post->tags : explode(',', $post->tags);
                                    ?>
                                    <?php foreach (array_slice($tags, 0, 3) as $tag): ?>
                                        <a href="<?= base_url('blog/tag/' . trim($tag)) ?>"
                                           class="badge bg-secondary text-decoration-none">
                                            <?= esc(trim($tag)) ?>
                                        </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                            <h3 class="card-title">
                                <a href="<?= base_url('blog/' . $post->slug) ?>" class="text-decoration-none text-dark">
                                    <?= esc($post->title) ?>
                                </a>
                            </h3>

                            <p class="text-muted mb-3">
                                <?= mb_substr(strip_tags($post->content), 0, 200) ?>...
                            </p>

                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-muted small">
                                    <i class="material-icons-outlined" style="font-size: 14px;">person</i>
                                    <?= esc($post->author_name ?? 'Admin') ?>
                                    <span class="mx-2">•</span>
                                    <i class="material-icons-outlined" style="font-size: 14px;">event</i>
                                    <?= date('d M Y', strtotime($post->published_at ?? $post->created_at)) ?>
                                    <span class="mx-2">•</span>
                                    <i class="material-icons-outlined" style="font-size: 14px;">visibility</i>
                                    <?= number_format($post->view_count ?? 0) ?> views
                                </div>
                                <a href="<?= base_url('blog/' . $post->slug) ?>" class="btn btn-outline-primary btn-sm">
                                    Baca Selengkapnya
                                    <i class="material-icons-outlined" style="font-size: 16px;">arrow_forward</i>
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>

                <!-- Pagination -->
                <?php if (isset($pager)): ?>
                    <div class="d-flex justify-content-center">
                        <?= $pager->links() ?>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="material-icons-outlined text-muted" style="font-size: 64px;">article</i>
                        <h4 class="mt-3 text-muted">Belum Ada Artikel</h4>
                        <p class="text-muted">
                            <?php if (!empty($category)): ?>
                                Belum ada artikel dalam kategori "<?= esc($category->name) ?>".
                            <?php else: ?>
                                Belum ada artikel dalam kategori ini.
                            <?php endif; ?>
                        </p>
                        <a href="<?= base_url('blog') ?>" class="btn btn-primary mt-3">
                            Lihat Semua Artikel
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- All Categories -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="material-icons-outlined">folder</i>
                        Semua Kategori
                    </h6>
                </div>
                <div class="list-group list-group-flush">
                    <?php if (!empty($all_categories)): ?>
                        <?php foreach ($all_categories as $cat): ?>
                            <a href="<?= base_url('blog/category/' . $cat->slug) ?>"
                               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?= (!empty($category) && $cat->id == $category->id) ? 'active' : '' ?>">
                                <?= esc($cat->name) ?>
                                <span class="badge <?= (!empty($category) && $cat->id == $category->id) ? 'bg-light text-dark' : 'bg-primary' ?> rounded-pill">
                                    <?= number_format($cat->post_count ?? 0) ?>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="list-group-item text-muted text-center">
                            Tidak ada kategori
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Popular Posts -->
            <?php if (!empty($popular_posts)): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="material-icons-outlined">trending_up</i>
                            Artikel Populer
                        </h6>
                    </div>
                    <div class="list-group list-group-flush">
                        <?php foreach ($popular_posts as $popular): ?>
                            <a href="<?= base_url('blog/' . $popular->slug) ?>"
                               class="list-group-item list-group-item-action">
                                <h6 class="mb-1"><?= esc($popular->title) ?></h6>
                                <small class="text-muted">
                                    <i class="material-icons-outlined" style="font-size: 12px;">visibility</i>
                                    <?= number_format($popular->view_count ?? 0) ?> views
                                    <span class="mx-1">•</span>
                                    <?= date('d M Y', strtotime($popular->published_at ?? $popular->created_at)) ?>
                                </small>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Popular Tags -->
            <?php if (!empty($popular_tags)): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="material-icons-outlined">label</i>
                            Tag Populer
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($popular_tags as $tag): ?>
                                <a href="<?= base_url('blog/tag/' . $tag->slug) ?>"
                                   class="badge bg-secondary text-decoration-none"
                                   style="font-size: 0.9rem;">
                                    <?= esc($tag->name) ?>
                                    <span class="badge bg-light text-dark ms-1"><?= number_format($tag->count ?? 0) ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Recent Posts -->
            <?php if (!empty($recent_posts)): ?>
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="material-icons-outlined">schedule</i>
                            Artikel Terbaru
                        </h6>
                    </div>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recent_posts as $recent): ?>
                            <a href="<?= base_url('blog/' . $recent->slug) ?>"
                               class="list-group-item list-group-item-action">
                                <h6 class="mb-1"><?= esc($recent->title) ?></h6>
                                <small class="text-muted">
                                    <i class="material-icons-outlined" style="font-size: 12px;">event</i>
                                    <?= date('d M Y', strtotime($recent->published_at ?? $recent->created_at)) ?>
                                </small>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
