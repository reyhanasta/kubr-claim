# UI Components Documentation

## Custom Error Pages

Aplikasi ini memiliki custom error pages yang responsive dan mengikuti design system:

### Available Error Pages

- **404.blade.php** - Page Not Found
- **500.blade.php** - Server Error (dengan debug info di local environment)
- **503.blade.php** - Maintenance Mode (dengan auto-refresh setiap 30 detik)

### Features
- ✅ Responsive design
- ✅ Dark mode support
- ✅ Quick actions (back, reload, go to dashboard)
- ✅ Auto-refresh untuk 503
- ✅ Debug info untuk 500 (hanya di local)

---

## Loading Skeleton Components

Reusable skeleton loaders untuk better UX saat loading data.

### Available Components

#### 1. Skeleton Text
```blade
<x-skeleton.text rows="3" />
```
**Props:**
- `rows` (default: 3) - Jumlah baris placeholder

#### 2. Skeleton Card
```blade
<x-skeleton.card count="3" />
```
**Props:**
- `count` (default: 1) - Jumlah card placeholder

#### 3. Skeleton Table
```blade
<x-skeleton.table columns="4" rows="5" />
```
**Props:**
- `columns` (default: 4) - Jumlah kolom
- `rows` (default: 5) - Jumlah baris data

#### 4. Skeleton Stats
```blade
<x-skeleton.stats count="4" />
```
**Props:**
- `count` (default: 4) - Jumlah stat cards

### Usage with Livewire

Gunakan dengan `wire:loading` untuk menampilkan skeleton saat loading:

```blade
<!-- Content -->
<div wire:loading.remove wire:target="filterMethod">
    <!-- Your actual content here -->
</div>

<!-- Loading Skeleton -->
<div wire:loading wire:target="filterMethod">
    <x-skeleton.stats count="3" />
</div>
```

### Customization

Semua skeleton components mendukung class merging:

```blade
<x-skeleton.card count="2" class="my-custom-class" />
```

---

## Best Practices

1. **Always provide loading states** - User harus tahu kalau ada proses yang berjalan
2. **Match skeleton dengan actual content** - Skeleton harus mirip dengan konten asli
3. **Use appropriate skeleton** - Pilih skeleton yang sesuai dengan tipe konten
4. **Test in slow network** - Test dengan throttling untuk memastikan skeleton terlihat

---

## Examples

### Dashboard Stats Loading
```blade
<div wire:loading.remove wire:target="year,month">
    <!-- Stats cards -->
</div>

<div wire:loading wire:target="year,month">
    <x-skeleton.stats count="3" />
</div>
```

### Table Loading
```blade
<div wire:loading.remove wire:target="search,sortBy">
    <table><!-- your table --></table>
</div>

<div wire:loading wire:target="search,sortBy">
    <x-skeleton.table columns="5" rows="10" />
</div>
```

### Form Loading
```blade
<form wire:submit="save">
    <div wire:loading.remove wire:target="save">
        <!-- Form fields -->
    </div>
    
    <div wire:loading wire:target="save">
        <x-skeleton.text rows="4" />
    </div>
</form>
```
