
<!-- start: Footer -->
<footer id="footer">
    <div class="container">
    <?php if (!OFFLINE) { ?>
		<div class="pull-center adbanner_web">
		
		<script type="text/javascript"><!--
		google_ad_client = "ca-pub-9874604497476880";
		/* Gap Year 365 */
		google_ad_slot = "2273353061";
		google_ad_width = 728;
		google_ad_height = 90;
		//-->
		</script>
		<script type="text/javascript"
		src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
		</script>
		</div>

                <div class="span8 adbanner_mob">
                <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
                <!-- One World365 Mobile Banner Header -->
                <ins class="adsbygoogle"
                     style="display:inline-block;width:320px;height:100px"
                     data-ad-client="ca-pub-9874604497476880"
                     data-ad-slot="1198653468"></ins>
                <script>
                (adsbygoogle = window.adsbygoogle || []).push({});
                </script>
                </div>
	<?php } ?>
    </div>

<?

$oFooterArticle = new Article;
$oFooterArticle->Get($_CONFIG['site_id'],'/footer');

?>

<div class="span12">
    <?= $oFooterArticle->GetDescFull(); ?>
</div>


</footer>
<!-- end: Footer -->

</div>
</div>
<!-- end: Container -->

</html>

