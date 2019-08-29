 <div class="featured news-item span4">

	<div class="img-responsive img-rounded" style="min-height: 240px;">
         <? if (is_object($this->Get("ARTICLE_OBJECT")->GetImage(0))) { ?>
          <a title="<?= $this->Get("TITLE") ?>" href="<?= $this->Get("URL") ?>">
                <?= $this->Get("ARTICLE_OBJECT")->GetImage(0)->GetHtml("_l",$this->Get("TITLE")); ?>
          </a>
         <? } else { 
            // try to grab an image from article body text
            $html = $this->Get("ARTICLE_OBJECT")->GetDescFull();
            $arrImgUrl = array();
            preg_match_all( '|<img.*?src=[\'"](.*?)[\'"].*?>|i',$html, $arrImgUrl );
            if (count($arrImgUrl[1]) >= 1)
            { ?>
				<a title="<?= $this->Get("TITLE") ?>" href="<?= $this->Get("URL") ?>">
					<img class='img-responsive img-rounded' src='<?= $arrImgUrl[1][0] ?>' alt='<?= $this->Get("TITLE") ?>' border='0' />
				</a><?php 
            }
        }?>
        </div>

        <h3 style="margin-top: 10px;"><a href="<?= $this->Get("URL"); ?>" title="<?= $this->Get("TITLE"); ?>"><?= $this->Get("TITLE"); ?></a></h3>
        <p style="font-size: 1.2em;"><?= substr(strip_tags($this->Get("DESC_SHORT_160")),0,110)."..."; ?></p>

</div>
