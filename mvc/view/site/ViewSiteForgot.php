<?php
namespace manguto\cms5\mvc\view\site;


class ViewSiteForgot extends ViewSite
{

    static function get_forgot()
    {
        self::PageSite("forgot", [
            'form_action' => '/forgot'
        ]);
    }

    static function get_forgot_sent($email, $emailUrl, $emailName)
    {
        self::PageSite("forgot-sent", [
            'email' => $email,
            'emailUrl' => 'http://' . $emailUrl,
            'emailName' => $emailName
        ]);
    }

    static function post_forgot_reset()
    {
        self::PageSite("forgot-reset-success", [
            'link_form_login' => '/login'
        ]);
    }
}