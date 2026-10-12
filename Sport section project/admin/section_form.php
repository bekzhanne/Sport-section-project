<?php
require __DIR__ . '/../includes/session.php';
require __DIR__ . '/../includes/db.php';

require_admin();

function slugify(string $value): string
{
    $slug = mb_strtolower(trim($value));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';

    return trim($slug, '-');
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
$section = [
    'id' => 0,
    'name' => '',
    'slug' => '',
    'description' => '',
    'instructor' => '',
    'location' => '',
    'capacity' => 20,
    'is_active' => 1,
];

if ($id) {
    $stmt = db()->prepare('SELECT * FROM sports_sections WHERE id = ?');
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if (!$found) {
        redirect('sections.php?status=not_found');
    }
    $section = $found;
}

$isEdit = $id > 0;
$errors = [];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $section['id'] = post_positive_int('id') ?? 0;
    $isEdit = $section['id'] > 0;
    $section['name'] = trim(post_string('name'));
    $section['instructor'] = trim(post_string('instructor'));
    $section['location'] = trim(post_string('location'));
    $section['description'] = trim(post_string('description'));
    $section['slug'] = trim(post_string('slug')) !== '' ? slugify(post_string('slug')) : slugify($section['name']);
    $section['is_active'] = isset($_POST['is_active']) ? 1 : 0;
    $capacity = filter_var($_POST['capacity'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 500]]);
    $section['capacity'] = $capacity !== false ? $capacity : $section['capacity'];

    if (!verify_csrf()) {
        $errors['form'] = 'Your session has expired. Please try again.';
    }

    if (mb_strlen($section['name']) < 1 || mb_strlen($section['name']) > 120) {
        $errors['name'] = 'Name must be between 1 and 120 characters.';
    }

    if (mb_strlen($section['instructor']) < 1 || mb_strlen($section['instructor']) > 120) {
        $errors['instructor'] = 'Instructor must be between 1 and 120 characters.';
    }

    if (mb_strlen($section['location']) < 1 || mb_strlen($section['location']) > 160) {
        $errors['location'] = 'Location must be between 1 and 160 characters.';
    }

    if (mb_strlen($section['description']) < 1 || mb_strlen($section['description']) > 2000) {
        $errors['description'] = 'Description must be between 1 and 2000 characters.';
    }

    if ($capacity === false) {
        $errors['capacity'] = 'Capacity must be a number between 1 and 500.';
    }

    if ($section['slug'] === '') {
        $errors['slug'] = 'Could not generate a URL slug from this name — please set one manually.';
    }

    if (!$errors) {
        try {
            if ($isEdit) {
                $stmt = db()->prepare(
                    'UPDATE sports_sections SET name = ?, slug = ?, description = ?, instructor = ?, location = ?, capacity = ?, is_active = ? WHERE id = ?'
                );
                $stmt->execute([
                    $section['name'], $section['slug'], $section['description'], $section['instructor'],
                    $section['location'], $section['capacity'], $section['is_active'], $section['id'],
                ]);
                redirect('sections.php?status=updated');
            }

            $stmt = db()->prepare(
                'INSERT INTO sports_sections (name, slug, description, instructor, location, capacity, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $section['name'], $section['slug'], $section['description'], $section['instructor'],
                $section['location'], $section['capacity'], $section['is_active'],
            ]);
            redirect('sections.php?status=created');
        } catch (PDOException $ex) {
            if ($ex->getCode() !== '23000') {
                throw $ex;
            }
            $errors['slug'] = 'That URL slug is already used by another section.';
        }
    }
}

$adminActive = 'sections';
$pageTitle = ($isEdit ? 'Edit section' : 'Add section') . ' · KBTU culturist';
require __DIR__ . '/../includes/header.php';
?>
        <main class="admin-section">
            <div class="container">
                <a class="back-link" href="sections.php">← All sections</a>

                <div class="admin-card">
                    <h2><?= $isEdit ? 'Edit section' : 'Add new section' ?></h2>

                    <?php if (isset($errors['form'])): ?>
                        <div class="form-alert"><?= e($errors['form']) ?></div>
                    <?php endif; ?>

                    <form method="post" class="auth-form" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="id" value="<?= (int) $section['id'] ?>">

                        <div class="form-grid">
                            <label class="form-group span-2">
                                <span class="form-label">Name</span>
                                <input type="text" name="name" class="form-input<?= isset($errors['name']) ? ' is-invalid' : '' ?>" value="<?= e($section['name']) ?>" maxlength="120" required>
                                <?php if (isset($errors['name'])): ?><span class="form-error"><?= e($errors['name']) ?></span><?php endif; ?>
                            </label>

                            <label class="form-group">
                                <span class="form-label">Instructor</span>
                                <input type="text" name="instructor" class="form-input<?= isset($errors['instructor']) ? ' is-invalid' : '' ?>" value="<?= e($section['instructor']) ?>" maxlength="120" required>
                                <?php if (isset($errors['instructor'])): ?><span class="form-error"><?= e($errors['instructor']) ?></span><?php endif; ?>
                            </label>

                            <label class="form-group">
                                <span class="form-label">Location</span>
                                <input type="text" name="location" class="form-input<?= isset($errors['location']) ? ' is-invalid' : '' ?>" value="<?= e($section['location']) ?>" maxlength="160" required>
                                <?php if (isset($errors['location'])): ?><span class="form-error"><?= e($errors['location']) ?></span><?php endif; ?>
                            </label>

                            <label class="form-group">
                                <span class="form-label">Capacity</span>
                                <input type="number" name="capacity" class="form-input<?= isset($errors['capacity']) ? ' is-invalid' : '' ?>" value="<?= (int) $section['capacity'] ?>" min="1" max="500" required>
                                <?php if (isset($errors['capacity'])): ?><span class="form-error"><?= e($errors['capacity']) ?></span><?php endif; ?>
                            </label>

                            <label class="form-group">
                                <span class="form-label">URL slug <span class="form-optional">(auto-generated if left blank)</span></span>
                                <input type="text" name="slug" class="form-input<?= isset($errors['slug']) ? ' is-invalid' : '' ?>" value="<?= e($section['slug']) ?>" maxlength="80" placeholder="e.g. strength-training">
                                <?php if (isset($errors['slug'])): ?><span class="form-error"><?= e($errors['slug']) ?></span><?php endif; ?>
                            </label>

                            <label class="form-group span-2">
                                <span class="form-label">Description</span>
                                <textarea name="description" class="form-input<?= isset($errors['description']) ? ' is-invalid' : '' ?>" maxlength="2000" required><?= e($section['description']) ?></textarea>
                                <?php if (isset($errors['description'])): ?><span class="form-error"><?= e($errors['description']) ?></span><?php endif; ?>
                            </label>

                            <label class="form-checkbox span-2">
                                <input type="checkbox" name="is_active" value="1" <?= $section['is_active'] ? 'checked' : '' ?>>
                                Active (visible to members)
                            </label>
                        </div>

                        <button type="submit" class="btn btn-navy auth-submit"><?= $isEdit ? 'Save changes' : 'Create section' ?></button>
                    </form>
                </div>
            </div>
        </main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
