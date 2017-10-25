<section class="header">
    <div class="container"> 
        <div class="row">
            <div class="col-sm-1">
                <a style="height:21px;" href="{{url('dashboard')}}">{{ image("img/covenant.png")}}</a>
            </div>
            <div class="col-sm-11">
                <ul class="navigation list-inline">
                   
                     {% if allowed['members'] %}
                        <li><a href="{{url('members')}}">Members</a></li>
                    {% endif %}
                    {% if allowed['loans'] %} 
                        <li><a href="{{url('loans')}}">Loans</a></li>
                    {% endif %}
                    {% if allowed['savings'] %}
                        <li><a href="{{url('savings')}}">Savings</a></li>
                    {% endif %}
                   
                    <li>
                        <a href="#" class="" data-toggle="dropdown"><i class="fa fa-user margin-right-sm"></i><span class="caret md"></span></a>
                        <ul class="dropdown-menu dropdown-menu-right">
                            <li class="dropdown-header"><a href="#"><strong>{{fullName}}</strong></a></li>
                            <li class="divider"></li>
                            <li><a id="btn_password_change" href="#">Change Password</a></li>
                            <li class="divider"></li>
                            <li><a href="{{url('logout')}}">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>