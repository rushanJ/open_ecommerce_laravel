<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Media;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CmsSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $pages = $this->seedPages();
            $this->seedMenus($pages);
            $this->seedBannersAndMedia();
        });
    }

    /**
     * @return array<string, Page>
     */
    private function seedPages(): array
    {
        $defs = [
            'about-us' => ['About Us', "open_ecommerce_laravel is a curated marketplace focused on quality products and reliable delivery.\n\nWe’re building a delightful Sri Lankan commerce experience—one step at a time."],
            'contact-us' => ['Contact Us', "Need help?\n\nEmail: support@open-ecommerce-laravel.test\nPhone: +94 77 000 0000\n\nWe usually respond within 1–2 business days."],
            'privacy-policy' => ['Privacy Policy', "We respect your privacy.\n\nThis is a demo policy. Replace with your real privacy policy before going live."],
            'terms-and-conditions' => ['Terms & Conditions', "By using open_ecommerce_laravel, you agree to these terms.\n\nThis is a demo placeholder. Replace with your legal terms before launch."],
            'refund-policy' => ['Refund Policy', "Refunds are available for eligible items.\n\nThis is a demo placeholder. Define your real refund rules and timelines."],
        ];

        $pages = [];
        foreach ($defs as $slug => [$title, $content]) {
            $page = Page::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $title,
                    'content' => $content,
                    'status' => 'published',
                    'meta_title' => $title,
                    'meta_description' => Str::limit(str_replace("\n", ' ', $content), 150),
                    'published_at' => now(),
                ]
            );
            $pages[$slug] = $page;
        }

        return $pages;
    }

    /**
     * @param  array<string, Page>  $pages
     */
    private function seedMenus(array $pages): void
    {
        $headerMenu = Menu::query()->updateOrCreate(
            ['name' => 'Default Header Menu'],
            ['location' => 'header', 'status' => 'active']
        );

        $footerMenu = Menu::query()->updateOrCreate(
            ['name' => 'Default Footer Menu'],
            ['location' => 'footer', 'status' => 'active']
        );

        // Deterministic seeded items: clear only these menus' items.
        MenuItem::query()->where('menu_id', $headerMenu->getKey())->delete();
        MenuItem::query()->where('menu_id', $footerMenu->getKey())->delete();

        $this->seedHeaderItems($headerMenu, $pages);
        $this->seedFooterItems($footerMenu, $pages);
    }

    /**
     * @param  array<string, Page>  $pages
     */
    private function seedHeaderItems(Menu $menu, array $pages): void
    {
        $items = [
            ['title' => 'Home', 'type' => 'custom', 'url' => '/'],
            ['title' => 'Shop', 'type' => 'custom', 'url' => '/shop'],
            ['title' => 'Categories', 'type' => 'custom', 'url' => '/categories'],
            ['title' => 'Brands', 'type' => 'custom', 'url' => '/brands'],
            ['title' => 'About Us', 'type' => 'page', 'reference_id' => $pages['about-us']->getKey()],
            ['title' => 'Contact Us', 'type' => 'page', 'reference_id' => $pages['contact-us']->getKey()],
        ];

        foreach ($items as $i => $row) {
            MenuItem::query()->create([
                'menu_id' => $menu->getKey(),
                'parent_id' => null,
                'title' => $row['title'],
                'type' => $row['type'],
                'reference_id' => $row['reference_id'] ?? null,
                'url' => $row['url'] ?? null,
                'target' => 'self',
                'sort_order' => $i * 10,
            ]);
        }
    }

    /**
     * @param  array<string, Page>  $pages
     */
    private function seedFooterItems(Menu $menu, array $pages): void
    {
        $items = [
            ['title' => 'Privacy Policy', 'type' => 'page', 'reference_id' => $pages['privacy-policy']->getKey()],
            ['title' => 'Terms & Conditions', 'type' => 'page', 'reference_id' => $pages['terms-and-conditions']->getKey()],
            ['title' => 'Refund Policy', 'type' => 'page', 'reference_id' => $pages['refund-policy']->getKey()],
            ['title' => 'Contact Us', 'type' => 'page', 'reference_id' => $pages['contact-us']->getKey()],
        ];

        foreach ($items as $i => $row) {
            MenuItem::query()->create([
                'menu_id' => $menu->getKey(),
                'parent_id' => null,
                'title' => $row['title'],
                'type' => $row['type'],
                'reference_id' => $row['reference_id'],
                'url' => null,
                'target' => 'self',
                'sort_order' => $i * 10,
            ]);
        }
    }

    private function seedBannersAndMedia(): void
    {
        $homeHero = Banner::query()->updateOrCreate(
            ['title' => 'Shop the latest', 'position' => 'home_hero'],
            [
                'subtitle' => 'Discover new arrivals and featured picks.',
                'image_path' => '/storage/banners/home-hero.jpg',
                'link_url' => '/shop',
                'starts_at' => null,
                'ends_at' => null,
                'status' => 'active',
            ]
        );

        $electronics = Category::query()->where('slug', 'electronics')->first()
            ?? Category::query()->where('name', 'like', '%Elect%')->first();
        if ($electronics) {
            Banner::query()->updateOrCreate(
                ['title' => 'Electronics Deals', 'position' => 'home_hero'],
                [
                    'subtitle' => 'Save on popular electronics.',
                    'image_path' => '/storage/banners/electronics.jpg',
                    'link_url' => '/categories/'.$electronics->slug,
                    'starts_at' => null,
                    'ends_at' => null,
                    'status' => 'active',
                ]
            );
        }

        $fashion = Category::query()->where('slug', 'fashion')->first()
            ?? Category::query()->where('name', 'like', '%Fashion%')->first();
        if ($fashion) {
            Banner::query()->updateOrCreate(
                ['title' => 'Fashion Picks', 'position' => 'home_hero'],
                [
                    'subtitle' => 'Trending styles curated for you.',
                    'image_path' => '/storage/banners/fashion.jpg',
                    'link_url' => '/categories/'.$fashion->slug,
                    'starts_at' => null,
                    'ends_at' => null,
                    'status' => 'active',
                ]
            );
        }

        $this->seedMediaRow('/storage/banners/home-hero.jpg', 'home-hero.jpg', 'image/jpeg');
        $this->seedMediaRow('/storage/banners/electronics.jpg', 'electronics.jpg', 'image/jpeg');
        $this->seedMediaRow('/storage/banners/fashion.jpg', 'fashion.jpg', 'image/jpeg');
    }

    private function seedMediaRow(string $path, string $filename, string $mime): void
    {
        Media::query()->updateOrCreate(
            ['disk' => 'public', 'path' => $path],
            [
                'filename' => $filename,
                'mime_type' => $mime,
                'size' => 0,
                'alt_text' => null,
                'uploaded_by_admin_id' => null,
            ]
        );
    }
}

