<?php

class actionUsersProfileKarma extends cmsAction {

    public function run($profile){

        $user = cmsUser::getInstance();

        $page = $this->request->get('page', 1);
        $perpage = 10;

        $total = $this->model->getKarmaLogCount($profile['id']);
        $log = $this->model->limitPage($page, $perpage)->getKarmaLog($profile['id']);

        $tabs = $this->controller->getProfileMenu($profile);

        cmsTemplate::getInstance()->render('profile_karma', array(
            'user'    => $user,
            'tabs'    => $tabs,
            'tab'     => $this->tabs['karma'],
            'profile' => $profile,
            'log'     => $log,
            'total'   => $total,
            'page'    => $page,
            'perpage' => $perpage
        ));

    }

}
