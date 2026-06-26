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
            $this->home($lang);
            $this->contact($lang);
            $this->publish($lang,'index');
            $this->publish($lang,'contact');
        }
    }

    public function down(): void {}

    private function home(string $lang): void
    {
        $base=DB::table('aa_page_builder_blocks')->where('lang_code',$lang)->where('page_key','index');
        $heroes=(clone $base)->where('block_type','home_hero')->orderBy('sort_order')->orderByDesc('id')->get();
        $hero=$heroes->first();
        if($heroes->count()>1)(clone $base)->where('block_type','home_hero')->where('id','<>',$hero->id)->delete();
        $grid=(clone $base)->where('block_type','home_content_grid')->first();
        if(!$grid){
            $uuid=(string)Str::uuid();
            DB::table('aa_page_builder_blocks')->insert($this->row($lang,'index',$uuid,'home_content_grid',[],2));
            $grid=(clone $base)->where('block_uuid',$uuid)->first();
        }
        foreach([['category_listing','main',1],['article_listing','main',2],['training_listing','sidebar',1]] as [$type,$slot,$sort]){
            (clone $base)->where('block_type',$type)->update(['parent_block_uuid'=>$grid->block_uuid,'slot_key'=>$slot,'sort_order'=>$sort,'updated_at'=>now()]);
        }
        if(!(clone $base)->where('block_type','home_journal')->exists())DB::table('aa_page_builder_blocks')->insert($this->row($lang,'index',(string)Str::uuid(),'home_journal',[],3,$grid->block_uuid,'main'));
        $sidebar=(clone $base)->where('block_type','advertisement_listing')->get()->first(function($row){
            $content=json_decode((string)$row->content_json,true)?:[]; return ($content['position']??'bottom')==='sidebar';
        });
        if(!$sidebar){
            DB::table('aa_page_builder_blocks')->insert($this->row($lang,'index',(string)Str::uuid(),'advertisement_listing',['position'=>'sidebar','limit'=>2],2,$grid->block_uuid,'sidebar'));
        }else{
            (clone $base)->where('id',$sidebar->id)->update(['parent_block_uuid'=>$grid->block_uuid,'slot_key'=>'sidebar','sort_order'=>2,'updated_at'=>now()]);
        }
        $bottom=(clone $base)->where('block_type','advertisement_listing')->get()->first(function($row){
            $content=json_decode((string)$row->content_json,true)?:[]; return ($content['position']??'bottom')==='bottom';
        });
        if($bottom)(clone $base)->where('id',$bottom->id)->update(['parent_block_uuid'=>null,'slot_key'=>'default','sort_order'=>3,'updated_at'=>now()]);
        if($hero)(clone $base)->where('id',$hero->id)->update(['parent_block_uuid'=>null,'slot_key'=>'default','sort_order'=>1,'updated_at'=>now()]);
        (clone $base)->where('id',$grid->id)->update(['parent_block_uuid'=>null,'slot_key'=>'default','sort_order'=>2,'updated_at'=>now()]);
        (clone $base)->where('block_type','feature_listing')->update(['parent_block_uuid'=>null,'slot_key'=>'default','sort_order'=>4,'updated_at'=>now()]);
        (clone $base)->where('block_type','partner_listing')->update(['parent_block_uuid'=>null,'slot_key'=>'default','sort_order'=>5,'updated_at'=>now()]);
    }

    private function contact(string $lang): void
    {
        $base=DB::table('aa_page_builder_blocks')->where('lang_code',$lang)->where('page_key','contact');
        $grid=(clone $base)->where('block_type','contact_grid')->first();
        if(!$grid){
            $uuid=(string)Str::uuid();
            DB::table('aa_page_builder_blocks')->insert($this->row($lang,'contact',$uuid,'contact_grid',[],1));
            $grid=(clone $base)->where('block_uuid',$uuid)->first();
        }
        foreach([['contact_info','info',1],['contact_form','form',2],['map_embed','map',3]] as [$type,$slot,$sort]){
            (clone $base)->where('block_type',$type)->update(['parent_block_uuid'=>$grid->block_uuid,'slot_key'=>$slot,'sort_order'=>$sort,'updated_at'=>now()]);
        }
        (clone $base)->where('id',$grid->id)->update(['parent_block_uuid'=>null,'slot_key'=>'default','sort_order'=>1,'updated_at'=>now()]);
    }

    private function row(string $lang,string $page,string $uuid,string $type,array $content,int $sort,?string $parent=null,string $slot='default'): array
    {
        return ['block_uuid'=>$uuid,'parent_block_uuid'=>$parent,'lang_code'=>$lang,'page_key'=>$page,'region_key'=>'main','block_type'=>$type,'schema_version'=>1,'pattern_source_id'=>null,'title'=>'','subtitle'=>'','body'=>'','content_json'=>json_encode($content,JSON_UNESCAPED_UNICODE),'image_path'=>null,'button_text'=>null,'button_url'=>null,'settings_json'=>json_encode(['theme'=>'surface','layout'=>'wide','align'=>'left','radius'=>'24','shadow'=>'none','animation'=>'none','image_position'=>'right','max_width'=>'full','spacing'=>'medium'],JSON_UNESCAPED_UNICODE),'slot_key'=>$slot,'sort_order'=>$sort,'is_active'=>true,'created_at'=>now(),'updated_at'=>now()];
    }

    private function publish(string $lang,string $page): void
    {
        $rows=DB::table('aa_page_builder_blocks')->where('lang_code',$lang)->where('page_key',$page)->orderBy('sort_order')->orderBy('id')->get()->map(fn($row)=>(array)$row)->all();
        $json=json_encode($rows,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        $publication=DB::table('aa_page_publications')->where('lang_code',$lang)->where('page_key',$page)->first();
        $version=$publication?((int)$publication->version+1):1;
        DB::table('aa_page_publications')->updateOrInsert(['lang_code'=>$lang,'page_key'=>$page],['version'=>$version,'blocks_json'=>$json,'published_at'=>now(),'created_at'=>$publication?->created_at??now(),'updated_at'=>now()]);
        DB::table('aa_page_revisions')->insert(['lang_code'=>$lang,'page_key'=>$page,'version'=>$version,'blocks_json'=>$json,'change_note'=>'Restore original public design block layout','created_at'=>now(),'updated_at'=>now()]);
    }
};
