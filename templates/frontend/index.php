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
        <div class="row">
            <nav role="navigation" class="navbar navbar-default navbar-fixed-top">
            <div class="container">
                        <div class="navbar-header">
                            <button type="button" data-target="#navbarCollapse" data-toggle="collapse" class="navbar-toggle">
                                <span class="sr-only">Toggle navigation</span>
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                            </button>
                        <a class="navbar-brand visible-xs" href="<?php echo $this->baseurl; ?>"><?php echo $app->getCfg('sitename'); ?></a> 
                         </div>
                        <div id="navbarCollapse" class="collapse navbar-collapse">
                             <jdoc:include type="modules" name="mainmenu" style="none" />
                        </div>
	            </div>
                </nav>
        </div>
	<?php endif; ?>
         <div class="row">
            <header class="main-header">
                <div class="container">
                        <div class="col-xs-3 col-sm-3">            
                        <?php if ($this->countModules('logo')) : ?>
                            <div id="logo"><a href="<?php echo $this->baseurl; ?>">
                                <jdoc:include type="modules" name="logo" style="standard" />
                                </a>
                            </div>
                        <?php endif; ?>
						</div>
                        <div class="hidden-xs col-sm-9"> 
                    	 <?php if ($this->countModules('header')): ?>
                            <div id="header" class="clearfix">
                             <jdoc:include type="modules" name="header" style="standard" />
                            </div>
                        <?php endif; ?>
                        </div>
                        <div class="col-xs-9 col-sm-9"> 
						<?php if ($this->countModules('search')) : ?>
                        <div id="search">
                                <jdoc:include type="modules" name="search" style="standard" />
                        </div>
                        <?php endif; ?>
                        </div>
                 </div>   
            </header>
            </div>
			<!-- Mainbody -->
		<div id="mainbody" class="clearfix"> 
      		<div class="row">
				<div class="container">
					<!-- Content Block -->
					<div id="content" class="col-md-9 col-md-push-3">
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
							<jdoc:include type="modules" name="left" style="standard" />
						</div>
					</div>
				<?php endif; ?>
					<?php if ($this->countModules('right')) : ?>
					<aside class="sidebar-right col-md-3">
						<jdoc:include type="modules" name="right" style="standard" />
					</aside>
					<?php endif; ?>
				</div>
			</div>
		</div>
</div>

<jdoc:include type="modules" name="debug" />
</body>

</html>
