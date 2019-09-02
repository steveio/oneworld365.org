		<!-- BEGIN NAV -->
		<div class="nav-collapse collapse">
                <ul class="nav pull-left">
                
				<?
				$aSection = $this->Get('SECTIONS');
				$idx = array("one","two","three","four","five","six","seven","eight","nine","ten");
				$class = 'dropdown';
				$id = 'section-%s-topnav';
				$i = 0;
				foreach($aSection as $oSection) {
				?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="<?= $oSection->GetLink(); ?>"><?= $oSection->GetTitle(); ?></a>
                        <ul class="dropdown-menu">
							<?
							foreach($oSection->GetSubSections() as $oSubSection) {
								if ($oSubSection->HasSubSections()) {
								?>
									<li class="dropdown-submenu">
									<a tabindex="-1" href="<?= $oSubSection->GetLink(); ?>"><?= $oSubSection->GetTitle(); ?></a>
									<ul class="dropdown-menu level2">
									<?php 
									foreach($oSubSection->GetSubSections() as $oSubSection) {
									?>
										<li><a tabindex="-1" href="<?= $oSubSection->GetLink(); ?>"><?= $oSubSection->GetTitle(); ?></a></li>
									<?php 
									}
									?>
									</ul>
								<?
								} else {
									?>
									<li><a href="<?= $oSubSection->GetLink(); ?>" title="<?= $oSubSection->GetTitle(); ?>"> <?= $oSubSection->GetTitle(); ?></a></li>
									<?
								}
							}
							?>
                        </ul>
                    </li>
								
				<?
				$i++;
				} 
				?>				
                </ul>
            </div>
			<!-- END NAV -->
			
			
