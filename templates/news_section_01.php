<!-- begin: news section 01 -->
<div class="row">

     <!-- start: Page section -->
     <section id="page-sidebar" class="pull-left span8">

        <!-- start: articles-->
		<?
		$oArticleCollection = $this->Get('ARTICLE_OBJECT')->oArticleCollection;
		if ($oArticleCollection->Count() >= 1) {
			$oArticleCollection->LoadTemplate("article_list_item_01.php");
			$aArticle = $oArticleCollection->GetArticles();
			foreach($aArticle as $oArticle) {
				$oArticle->LoadTemplate("article_list_item_01.php");
				print $oArticle->Render();
			
			}
				
			print $oArticleCollection->Render();
		}
		?>      
     	<!-- end: articles -->
	</section>
     
	<!-- start: Sidebar -->
	<!-- 
        <aside id="sidebar" class="pull-right span4">

            <section class="">
                <h3 class="">Sidebar</h3>
     
     		</section>
     	</aside>
      -->
     <!--  end: Sidebar -->
</div>
<!--  end: news section 01 -->

