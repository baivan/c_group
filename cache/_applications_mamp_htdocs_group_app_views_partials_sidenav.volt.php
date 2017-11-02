<section class="header">
    <div class="container"> 
        <div class="row">
            <div class="col-sm-1">
                <a style="height:21px;" href="<?= $this->url->get('dashboard') ?>"><?= $this->tag->image(['img/covenant.png']) ?></a>
            </div>
            <div class="col-sm-11">
                <ul class="navigation list-inline">
                   
                     <?php if ($allowed['members']) { ?>
                        <li><a href="<?= $this->url->get('members') ?>">Members</a></li>
                    <?php } ?>
                    <?php if ($allowed['loans']) { ?> 
                        <li><a href="<?= $this->url->get('loans') ?>">Loans</a></li>
                    <?php } ?>
                    <?php if ($allowed['savings']) { ?>
                        <li><a href="<?= $this->url->get('savings') ?>">Savings</a></li>
                    <?php } ?>
                   
                    <li>
                        <a href="#" class="" data-toggle="dropdown"><i class="fa fa-user margin-right-sm"></i><span class="caret md"></span></a>
                        <ul class="dropdown-menu dropdown-menu-right">
                            <li class="dropdown-header"><a href="#"><strong><?= $fullName ?></strong></a></li>
                            <li class="divider"></li>
                            <li><a id="btn_password_change" href="#">Change Password</a></li>
                            <li class="divider"></li>
                            <li><a href="<?= $this->url->get('logout') ?>">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>