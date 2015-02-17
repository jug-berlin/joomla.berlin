<?php defined( '_JEXEC' ) or die; 

include_once JPATH_THEMES.'/'.$this->template.'/logic.php';
?><!doctype html>

<html lang="<?php echo $this->language; ?>">

<head>
	<jdoc:include type="head" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
	<link rel="apple-touch-icon-precomposed" href="<?php echo $tpath; ?>/images/apple-touch-icon-57x57-precomposed.png">
	<link rel="apple-touch-icon-precomposed" sizes="72x72" href="<?php echo $tpath; ?>/images/apple-touch-icon-72x72-precomposed.png">
	<link rel="apple-touch-icon-precomposed" sizes="114x114" href="<?php echo $tpath; ?>/images/apple-touch-icon-114x114-precomposed.png">
	<link rel="apple-touch-icon-precomposed" sizes="144x144" href="<?php echo $tpath; ?>/images/apple-touch-icon-144x144-precomposed.png">
	<!-- Le HTML5 shim and media query for IE8 support -->
	<!--[if lt IE 9]>
	<script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<script type="text/javascript" src="<?php echo $tpath; ?>/js/respond.min.js"></script>
	<![endif]-->
  </head>
<body class="<?php echo (($menu->getActive() == $menu->getDefault()) ? ('front') : ('site')).' '.$active->alias.' '.$pageclass; ?>" role="document">
     <div class="wrapper" id="page">
     	<?php if ($this->countModules('mainmenu')): ?>
            <nav role="navigation" class="navbar navbar-default navbar-fixed-top">
            <div class="container">
                        <div class="navbar-header">
  							 <button type="button" data-toggle="modal" href="#searchModal" class="btn btn-default search-toggle visible-xs"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>
                            <button type="button" data-target="#navbarCollapse" data-toggle="collapse" class="btn btn-default offcanvas-toggle  visible-xs">
                                <span class="sr-only">Toggle navigation</span>
                                <span class="glyphicon glyphicon-align-justify" aria-hidden="true"></span>
                            </button>
                        <a class="navbar-brand visible-xs" href="<?php echo $this->baseurl; ?>"><?php echo $app->getCfg('sitename'); ?></a> 
                         </div>
                        <div id="navbarCollapse" class="collapse navbar-collapse">
                             <jdoc:include type="modules" name="mainmenu" style="none" />
							<?php if ($this->countModules('menubar-search')) : ?>
                            <div id="menubar-search">
                             <!-- Button trigger modal -->
 							 <button type="button" data-toggle="modal" href="#searchModal" class="btn btn-default visible-sm-block search-toggle"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>
                            <div class="hidden-sm hidden-xs">
                            <jdoc:include type="modules" name="menubar-search" style="standard" />
                            </div>
                            </div>
                            <?php endif; ?>
	            </div>
                </nav>
	<?php endif; ?>
                <header class="container main-header">
                <div class="row">
                        <div  id="logo" class="col-xs-12 col-sm-5">            
                        <?php if ($this->countModules('logo')) : ?>
                        <div class="vertbottom">
                                <jdoc:include type="modules" name="logo" style="well" />
                         </div>
                         <?php endif; ?>
						</div>
						<div id="slogan" class="col-xs-12 col-sm-7"> 
                    	 <?php if ($this->countModules('header')): ?>
                        <div class="vertbottom">                        
                             <jdoc:include type="modules" name="header" style="well" />
                             </div>
                        <?php endif; ?>
                        </div>                        
                        </div>
            </header>
			<!-- Mainbody -->
		<div id="mainbody" class="clearfix"> 
				<div class="container">
                <div class="row">
					<!-- Content Block -->
					<div id="content" class="<?php if ($this->countModules('left')): echo "col-md-9 col-md-push-3"; else: echo "col-xs-12"; ;endif?>">
						<div id="message-component">
							<jdoc:include type="message" />
						</div>
					<?php if ($this->countModules('above-content')): ?>
						<div id="above-content">
							<jdoc:include type="modules" name="above-content" style="standard" />
						</div>
					<?php endif; ?>
							<div id="content-area">
								<jdoc:include type="component" />
							</div>
					<?php if ($this->countModules('below-content')): ?>
						<div id="below-content">
							<jdoc:include type="modules" name="below-content" style="standard" />
						</div>
					<?php endif; ?>
					</div>
                    <?php if ($this->countModules('left')): ?>
					<div class="sidebar-left col-md-3 col-md-pull-9">
						<div class="sidebar-nav">
							<jdoc:include type="modules" name="left" style="well" />
						</div>
					</div>
				<?php endif; ?>
                   </div>
				</div>
			</div>
            		<footer id="footer" class="clearfix">
			<?php if ($this->countModules('footer')): ?>
				
					<div class="container">
                    <div class="row">
                    <div class="col-xs-12">
						<jdoc:include type="modules" name="footer" style="standard" />
                    </div>    
					</div>
                    </div>
				
			<?php endif; ?>
		</footer>

</div>

</div><jdoc:include type="modules" name="debug" />
  <!-- Modal -->
  <div class="modal fade" id="searchModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title">JUG Berlin durchsuchen</h4>
        </div>
        <div class="modal-body">
           <jdoc:include type="modules" name="menubar-search" style="standard" />
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->
</body>

</html>
