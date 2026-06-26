<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach(DB::table('aa_languages')->where('is_active',true)->pluck('code') as $lang){
            $settings=DB::table('aa_settings')->where('lang_code',$lang)->pluck('setting_value','setting_key');
            $autoplay=(int)($settings['home_slider_autoplay_ms']??6200);
            if($autoplay<2500||$autoplay>30000)$autoplay=6200;
            $this->content($lang,'index','home_hero',['show_stats'=>true,'autoplay_ms'=>$autoplay]);
            $this->content($lang,'contact','contact_form',['title'=>(string)($settings['contact_form_title']??'Mesaj göndərin'),'text'=>'']);
            $map=DB::table('aa_page_builder_blocks')->where('lang_code',$lang)->where('page_key','contact')->where('block_type','map_embed')->first();
            if($map){
                $content=json_decode((string)$map->content_json,true)?:[];
                $content['title']=(string)($settings['home_map_title']??'Ünvan xəritəsi');
                DB::table('aa_page_builder_blocks')->where('id',$map->id)->update(['title'=>$content['title'],'content_json'=>json_encode($content,JSON_UNESCAPED_UNICODE),'updated_at'=>now()]);
            }
            $this->publish($lang,'index');
            $this->publish($lang,'contact');
        }
    }

    public function down(): void {}

    private function content(string $lang,string $page,string $type,array $content): void
    {
        DB::table('aa_page_builder_blocks')->where('lang_code',$lang)->where('page_key',$page)->where('block_type',$type)->update(['title'=>(string)($content['title']??''),'body'=>(string)($content['text']??''),'content_json'=>json_encode($content,JSON_UNESCAPED_UNICODE),'updated_at'=>now()]);
    }

    private function publish(string $lang,string $page): void
    {
        $rows=DB::table('aa_page_builder_blocks')->where('lang_code',$lang)->where('page_key',$page)->orderBy('sort_order')->orderBy('id')->get()->map(fn($row)=>(array)$row)->all();
        $json=json_encode($rows,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        $publication=DB::table('aa_page_publications')->where('lang_code',$lang)->where('page_key',$page)->first();
        $version=$publication?((int)$publication->version+1):1;
        DB::table('aa_page_publications')->updateOrInsert(['lang_code'=>$lang,'page_key'=>$page],['version'=>$version,'blocks_json'=>$json,'published_at'=>now(),'created_at'=>$publication?->created_at??now(),'updated_at'=>now()]);
        DB::table('aa_page_revisions')->insert(['lang_code'=>$lang,'page_key'=>$page,'version'=>$version,'blocks_json'=>$json,'change_note'=>'Normalize restored public design content','created_at'=>now(),'updated_at'=>now()]);
    }
};
