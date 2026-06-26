<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $languages=DB::table('aa_languages')->where('is_active',true)->pluck('code');
        foreach($languages as $lang){
            $settings=DB::table('aa_settings')->where('lang_code',$lang)->pluck('setting_value','setting_key');
            $pages=DB::table('aa_pages')->where('lang_code',$lang)->get()->keyBy('page_key');
            $page=fn(string $key,string $field,string $fallback='')=>(string)($pages->get($key)?->{$field}??$fallback);
            $setting=fn(string $key,string $fallback='')=>(string)($settings[$key]??$fallback);
            $map=[
                '__header'=>[['site_header',[]]],
                'index'=>[
                    ['home_hero',['show_stats'=>true,'autoplay_ms'=>(int)$setting('home_slider_autoplay_ms','6200')]],
                    ['category_listing',['title'=>$setting('section_academic','Akademik yazılar'),'limit'=>6]],
                    ['article_listing',['title'=>$setting('section_latest','Son dərc olunanlar'),'limit'=>4]],
                    ['training_listing',['title'=>$setting('section_trainings','Təlimlər'),'limit'=>3]],
                    ['advertisement_listing',['position'=>'bottom','limit'=>4]],
                    ['feature_listing',['title'=>$setting('section_features','Akademik imkanlarımız'),'show_support'=>true]],
                    ['partner_listing',['title'=>$setting('section_partners','Tərəfdaşlar'),'subtitle'=>$setting('section_partners_subtitle'),'limit'=>20]],
                ],
                'about'=>[['page_content',['title'=>'','subtitle'=>'','html'=>'','show_image'=>true]]],
                'articles'=>[['article_archive',['title'=>$setting('section_academic','Akademik yazılar'),'intro'=>$setting('site_description'),'limit'=>12]]],
                'article_detail'=>[['article_detail',['show_cover'=>true,'show_meta'=>true,'show_category'=>true]]],
                'certificates'=>[['certificate_lookup',['title'=>$page('certificates','title','Sertifikatlar'),'subtitle'=>$page('certificates','subtitle'),'show_points'=>true]]],
                'trainings'=>[['training_listing',['title'=>$page('trainings','title','Təlimlər'),'limit'=>24]]],
                'gallery'=>[['gallery_listing',['title'=>$setting('gallery_title','Qalereya'),'intro'=>$setting('gallery_intro'),'columns'=>'4']]],
                'contact'=>[
                    ['contact_info',['title'=>$page('contact','title','Əlaqə'),'html'=>$page('contact','body')]],
                    ['contact_form',['title'=>$setting('contact_form_title','Mesaj göndərin'),'text'=>'']],
                    ['map_embed',['title'=>$setting('home_map_title','Ünvan xəritəsi'),'embed_url'=>$this->mapUrl($setting('home_map_embed'))]],
                ],
                'profile'=>[['profile_card',['title'=>'','show_email'=>true,'show_logout'=>true]]],
                '__footer'=>[['site_footer',[]]],
            ];

            foreach($map as $pageKey=>$definitions){
                DB::table('aa_page_builder_blocks')->where('lang_code',$lang)->where('page_key',$pageKey)->delete();
                foreach($definitions as $index=>[$type,$content]){
                    DB::table('aa_page_builder_blocks')->insert([
                        'block_uuid'=>(string)Str::uuid(),'parent_block_uuid'=>null,'lang_code'=>$lang,'page_key'=>$pageKey,
                        'region_key'=>str_starts_with($pageKey,'__')?'template':'main','block_type'=>$type,'schema_version'=>1,
                        'pattern_source_id'=>null,'title'=>(string)($content['title']??''),'subtitle'=>(string)($content['subtitle']??''),
                        'body'=>(string)($content['html']??$content['text']??''),'content_json'=>json_encode($content,JSON_UNESCAPED_UNICODE),
                        'image_path'=>null,'button_text'=>null,'button_url'=>null,
                        'settings_json'=>json_encode(['theme'=>'surface','layout'=>'wide','align'=>'left','radius'=>'24','shadow'=>'none','animation'=>'none','image_position'=>'right','max_width'=>'full','spacing'=>'medium'],JSON_UNESCAPED_UNICODE),
                        'slot_key'=>'default','sort_order'=>$index+1,'is_active'=>true,'created_at'=>now(),'updated_at'=>now(),
                    ]);
                }
                $rows=DB::table('aa_page_builder_blocks')->where('lang_code',$lang)->where('page_key',$pageKey)->orderBy('sort_order')->get()->map(fn($row)=>(array)$row)->all();
                $json=json_encode($rows,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
                $publication=DB::table('aa_page_publications')->where('lang_code',$lang)->where('page_key',$pageKey)->first();
                $version=$publication?((int)$publication->version+1):1;
                DB::table('aa_page_publications')->updateOrInsert(['lang_code'=>$lang,'page_key'=>$pageKey],[
                    'version'=>$version,'blocks_json'=>$json,'published_at'=>now(),'created_at'=>$publication?->created_at??now(),'updated_at'=>now(),
                ]);
                DB::table('aa_page_revisions')->insert([
                    'lang_code'=>$lang,'page_key'=>$pageKey,'version'=>$version,'blocks_json'=>$json,
                    'change_note'=>'Full structured public site conversion','created_at'=>now(),'updated_at'=>now(),
                ]);
            }
        }
    }

    public function down(): void {}

    private function mapUrl(string $html): string
    {
        return preg_match('/src=["\']([^"\']+)["\']/i',$html,$match)?html_entity_decode($match[1]):'';
    }
};
