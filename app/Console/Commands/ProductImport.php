<?php

namespace App\Console\Commands;

use App\Models\Directory;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use JsonMachine\Exception\InvalidArgumentException;
use JsonMachine\Items;
use stdClass;

class ProductImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:import {--with-dirs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        if ($this->option("with-dirs")) {
            $this->importDirs();
        }

        $this->importProducts();

        return 0;
    }

    private function importProducts()
    {
        $products_root_dir = base_path("data/output/");

        foreach (scandir($products_root_dir) as $index => $products_sub_dir) {
            $sub_dir_full_path = $products_root_dir . $products_sub_dir;
            if ($products_sub_dir == "menu" or str_starts_with($products_sub_dir, ".") or is_file($sub_dir_full_path)) {
                continue;
            }

            $data_file_path = $sub_dir_full_path . "/data.json";
            if (!is_file($data_file_path)) {
                echo "No data file exists in $data_file_path\n";
                continue;
            }

            $data = json_decode(file_get_contents($data_file_path));
            if (!isset($data->code)) {
                echo "The product data file is old and must not be added to the database: $data_file_path \n";
                continue;
            }
            dd($data);
        }
    }

    private function importDirs()
    {
        $menu_file_dir = base_path("data/output/menu/");

        foreach (scandir($menu_file_dir) as $index => $menu_file_path) {
            $menu_file_full_path = $menu_file_dir . $menu_file_path;
            if (is_dir($menu_file_full_path))
                continue;

            $site_address = str_replace(["menu.", ".json"], "", $menu_file_path);
            $site_title = str_replace(["www.", ".com"], "", $site_address);

            $head_directory = Directory::where("url_full", "/" . $site_title)->first();
            if ($head_directory == null) {
                $head_directory = Directory::create([
                    "title" => $site_title, "url_part" => $site_title, "is_internal_link" => false, "is_anonymously_accessible" => true,
                    "has_web_page" => false, "priority" => $index, "content_type" => 3, "directory_id" => null,
                    "show_in_navbar" => true, "show_in_footer" => false, "cover_image_path" => null, "description" => "",
                    "data_type" => 1, "show_in_app_navbar" => false, "has_discount" => false, "is_location_limited" => false,
                    "cmc_id" => null, "force_show_landing" => false, "inaccessibility_type" => 1, "notice" => "",
                    "metadata" => $site_address
                ]);
                $head_directory->setUrlFull();
            }

            foreach (Items::fromFile($menu_file_full_path) as $head) {
                $this->createProductDirectory($head, $head_directory);
            }

        }
    }

    private function createProductDirectory(stdClass $node, Directory|Model $directory)
    {
        $title = strip_tags($node?->data?->title);
        $url_part = strtolower(str_replace([" • ", "\"", "'", " ", ".", "_", "\t", "\n", "@", "#", "%", "!", "?", "^", "&", "*", "(", ")", "=", "+", "•"], "-", $title));

        $head_directory = Directory::where("url_full", $directory->url_full . "/" . $url_part)->first();
        if ($head_directory == null) {
            $head_directory = $directory->directories()->create([
                "title" => $title, "url_part" => $url_part, "is_internal_link" => false, "is_anonymously_accessible" => true,
                "has_web_page" => false, "priority" => 0, "content_type" => 3, "directory_id" => null,
                "show_in_navbar" => true, "show_in_footer" => false, "cover_image_path" => null, "description" => "",
                "data_type" => 1, "show_in_app_navbar" => false, "is_location_limited" => false,
                "cmc_id" => null, "force_show_landing" => false, "inaccessibility_type" => 1, "notice" => "",
                "metadata" => $node?->data?->url
            ]);
            $head_directory->setUrlFull();
        }

        foreach ($node->sub_nodes as $sub_node) {
            $this->createProductDirectory($sub_node, $head_directory);
        }
    }
}
