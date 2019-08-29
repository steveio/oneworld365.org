      <!-- BEGIN article_summary_01 -->
      <div class="span4" style="height: 520px; padding-right: 20px;">

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

                        <h2><a href="<?= $this->Get("URL"); ?>" title="<?= $this->Get("TITLE"); ?>"><?= $this->Get("TITLE"); ?></a></h2>
                        <p><?= $this->Get("DESC_SHORT_160"); ?></p>
                </div>
      <!-- END article_summary_01 -->

