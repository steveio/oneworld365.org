<?
$oArticle = $this->Get('ARTICLE_OBJECT');
if(is_object($oArticle) && ($oArticle instanceof Article)) {
?>
			<article class="blog-post">
                <h2 class="post-title"><a href="<?= $oArticle->GetUrl(); ?>" title="View <?= $oArticle->GetTitle(); ?>"><?= $oArticle->GetTitle(); ?></a></h2>
                <div class="blog-post-inner">
                    <div class="post-media">
                        <div id="mainslider" class="flexslider ps-slider">
                            <ul class="slides unstyled">
                            <?
							if (is_object($oArticle->GetImage(0))) {
								print "<li><a href='". $oArticle->GetUrl()."' title='View '".$oArticle->GetTitle()."'>";
								print $oArticle->GetImage(0)->GetHtml("",$oArticle->GetTitle());
								print "</a></li>";
							} 
							?>
                            </ul>
                        </div>
                    </div>
                    <div class="post-content">
                        <div class="row-fluid">
                            <ul class="post-meta">
                            </ul>
                            <p class="lead">
                                <?= $oArticle->GetDescShort(); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </article>
<?php
}
?>