<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>

<!-- Hero Section -->
<div class="bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 mb-3">
                    <i class="material-icons-outlined">label</i>
                    <?php if (!empty($tag)): ?>
                        Tag: <?= esc($tag->name ?? $tag) ?>
                    <?php else: ?>
                        Tag Blog
                    <?php endif; ?>
                </h1>
                <p class="lead">
                    Artikel dengan tag "<?= esc($tag->name ?? $tag ?? '') ?>"
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
            <!-- Tag Info -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1">
                                <span class="badge bg-primary" style="font-size: 1.2rem;">
                                    <i class="material-icons-outlined">label</i>
                                    <?= esc($tag->name ?? $tag ?? '') ?>
                                </span>
                            </h5>
                            <p class="text-muted mb-0 mt-2">
                                <?= number_format($total_posts ?? 0) ?> artikel dengan tag ini
                            </p>
                        </div>
                    </div>
                </div>
            </div>

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
                                <?php if (!empty($post->category_name)): ?>
                                    <a href="<?= base_url('blog/category/' . $post->category_slug) ?>"
                                       class="badge bg-primary text-decoration-none">
                                        <?= esc($post->category_name) ?>
                                    </a>
                                <?php endif; ?>

                                <?php if (!empty($post->tags)): ?>
                                    <?php
                                    $tags = is_array($post->tags) ? $post->tags : explode(',', $post->tags);
                                    ?>
                                    <?php foreach (array_slice($tags, 0, 3) as $postTag): ?>
                                        <?php $currentTag = trim($postTag); ?>
                                        <a href="<?= base_url('blog/tag/' . $currentTag) ?>"
                                           class="badge <?= strtolower($currentTag) == strtolower($tag->name ?? $tag ?? '') ? 'bg-dark' : 'bg-secondary' ?> text-decoration-none">
                                            <?= esc($currentTag) ?>
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
                                    <?php if (!empty($post->comment_count)): ?>
                                        <span class="mx-2">•</span>
                                        <i class="material-icons-outlined" style="font-size: 14px;">comment</i>
                                        <?= number_format($post->comment_count) ?> komentar
                                    <?php endif; ?>
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
                        <i class="material-icons-outlined text-muted" style="font-size: 64px;">label_off</i>
                        <h4 class="mt-3 text-muted">Belum Ada Artikel</h4>
                        <p class="text-muted">
                            Belum ada artikel dengan tag "<?= esc($tag->name ?? $tag ?? '') ?>".
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
            <!-- Search -->
            <div class="card mb-4">
                <div class="card-body">
                    <form action="<?= base_url('blog/search') ?>" method="GET">
                        <div class="input-group">
                            <input type="text"
                                   class="form-control"
                                   name="q"
                                   placeholder="Cari artikel..."
                                   required>
                            <button class="btn btn-primary" type="submit">
                                <i class="material-icons-outlined">search</i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Related Tags -->
            <?php if (!empty($related_tags)): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="material-icons-outlined">label</i>
                            Tag Terkait
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($related_tags as $relatedTag): ?>
                                <a href="<?= base_url('blog/tag/' . $relatedTag->slug) ?>"
                                   class="badge bg-secondary text-decoration-none"
                                   style="font-size: 0.9rem;">
                                    <?= esc($relatedTag->name) ?>
                                    <span class="badge bg-light text-dark ms-1"><?= number_format($relatedTag->count ?? 0) ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- All Tags Cloud -->
            <?php if (!empty($all_tags)): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="material-icons-outlined">cloud</i>
                            Semua Tag
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($all_tags as $allTag): ?>
                                <?php
                                $isCurrent = strtolower($allTag->name) == strtolower($tag->name ?? $tag ?? '');
                                $fontSize = 0.8 + min(($allTag->count ?? 0) / 10, 0.5); // Scale font size by count
                                ?>
                                <a href="<?= base_url('blog/tag/' . $allTag->slug) ?>"
                                   class="badge <?= $isCurrent ? 'bg-dark' : 'bg-light text-dark' ?> text-decoration-none"
                                   style="font-size: <?= $fontSize ?>rem;">
                                    <?= esc($allTag->name) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Categories -->
            <?php if (!empty($categories)): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="material-icons-outlined">folder</i>
                            Kategori
                        </h6>
                    </div>
                    <div class="list-group list-group-flush">
                        <?php foreach ($categories as $category): ?>
                            <a href="<?= base_url('blog/category/' . $category->slug) ?>"
                               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <?= esc($category->name) ?>
                                <span class="badge bg-primary rounded-pill">
                                    <?= number_format($category->post_count ?? 0) ?>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

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
                                </small>
                            </a>
                        <?php endforeach; ?>
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
                                <?php if (!empty($recent->featured_image)): ?>
                                    <div class="row g-0">
                                        <div class="col-4">
                                            <img src="<?= base_url('uploads/blog/' . $recent->featured_image) ?>"
                                                 class="img-fluid rounded"
                                                 alt="<?= esc($recent->title) ?>"
                                                 style="height: 60px; object-fit: cover; width: 100%;">
                                        </div>
                                        <div class="col-8 ps-2">
                                            <h6 class="mb-1 small"><?= esc(mb_substr($recent->title, 0, 50)) ?>...</h6>
                                            <small class="text-muted">
                                                <?= date('d M Y', strtotime($recent->published_at ?? $recent->created_at)) ?>
                                            </small>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <h6 class="mb-1"><?= esc($recent->title) ?></h6>
                                    <small class="text-muted">
                                        <i class="material-icons-outlined" style="font-size: 12px;">event</i>
                                        <?= date('d M Y', strtotime($recent->published_at ?? $recent->created_at)) ?>
                                    </small>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
