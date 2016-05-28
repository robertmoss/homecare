<?php 


?>
<nav class="navbar navbar-default">
	<div class="container-fluid">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar1" aria-expanded="false">
	        	<span class="sr-only">Toggle navigation</span>
	        	<span class="icon-bar"></span>
	        	<span class="icon-bar"></span>
	        	<span class="icon-bar"></span>
      		</button>
			<a class="navbar-brand" href="<?php echo Config::$site_root ?>/index.php"><?php
			  $icon = Utility::getTenantProperty($applicationID, $tenantID, $userID,'icon');
              $title = ucfirst(Utility::getTenantProperty($applicationID, $tenantID, $userID,'title'));
              if (strlen($icon)>0) {
                  echo '<img src="' . $icon . '" alt=""' . $title . '" />';
              }
              else {
                  echo $title;
              }
		    ?></a>
		</div>
		<div class="collapse navbar-collapse" id="navbar1">
			<ul class="nav navbar-nav">
			    <?php
                $menu = Utility::getTenantMenu($applicationID, $userID, $tenantID); 
                if (is_array($menu)) {
                    foreach($menu as $item) {
                        $roles = $item["roles"];
                        if ($roles=='') {
                            $className = '';
                            if ($thisPage==$item["name"]) {
                                $className ='class="active"';
                                }
                            $link = strtolower($item["link"]);
                            if (substring($link,7)=="http://" || substring($link,8)=="https://") {
                                // not a relative link
                            }
                            else {
                                // account for fact that header is nested one folder below root
                                $link = Config::$site_root . "/ ". $link;
                                echo "<li>" . $link . "</li>";
                            }
                        echo '<li ' . $className . '><a href=" ' . $link . '">' . $item["name"] . '</a></li>';
                        }
                    }
                }
                ?>
                <li <?php if($thisPage=='admin') echo ' class="active"'?>><a href="<?php echo Config::$site_root ?>/admin/admin.php">Admin</a></li>
                
			</ul>
	     </div>
	</div>
</nav>
