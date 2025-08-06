<?php

namespace App\Services;

use App\Enums\EmailTemplateKey;
use App\Traits\FileManagerTrait;

class EmailTemplateService
{
    use FileManagerTrait;
    public function getEmailTemplateData($userType):array
    {
        $admin = EmailTemplateKey::ADMIN_EMAIL_LIST;
        $vendor = EmailTemplateKey::VENDOR_EMAIL_LIST;
        $customer = EmailTemplateKey::CUSTOMER_EMAIL_LIST;
        $deliveryMan = EmailTemplateKey::DELIVERY_MAN_EMAIL_LIST;

        return match ($userType) {
            'admin' => $admin,
            'customer' => $customer,
            'vendor' => $vendor,
            'delivery-man' => $deliveryMan,
        };
    }
    public function getHiddenField(string $userType, string $templateName):array|null
    {

        $hiddenData =  [
            EmailTemplateKey::REGISTRATION=>array('product_information','order_information','button_content','banner_image'),
            EmailTemplateKey::REGISTRATION_VERIFICATION=>array('product_information','order_information','button_content','banner_image'),
            EmailTemplateKey::REGISTRATION_FROM_POS=>array('product_information','order_information','button_url','button_content_status','banner_image'),
            EmailTemplateKey::REGISTRATION_APPROVED=>array('product_information','order_information','button_content','banner_image'),
            EmailTemplateKey::REGISTRATION_DENIED=>array('product_information','order_information','button_content','banner_image'),
            EmailTemplateKey::ACCOUNT_ACTIVATION=>array('product_information','order_information','button_content','banner_image'),
            EmailTemplateKey::ACCOUNT_SUSPENDED=>array('product_information','order_information','button_content','banner_image'),
            EmailTemplateKey::ACCOUNT_UNBLOCK=>array('product_information','order_information','button_content','banner_image'),
            EmailTemplateKey::ACCOUNT_BLOCK=>array('product_information','order_information','button_content','banner_image'),
            EmailTemplateKey::DIGITAL_PRODUCT_DOWNLOAD=>array('product_information','button_content','banner_image'),
            EmailTemplateKey::DIGITAL_PRODUCT_OTP=>array('product_information','order_information','button_content','banner_image'),
            EmailTemplateKey::ORDER_PLACE=>array('icon','product_information','banner_image'),
            EmailTemplateKey::ORDER_DElIVERED=>array('product_information','order_information','button_content','banner_image'),
            EmailTemplateKey::FORGET_PASSWORD=>array('product_information','order_information','button_content','banner_image'),
            EmailTemplateKey::ORDER_RECEIVED=>array('icon','product_information','button_content','banner_image'),
            EmailTemplateKey::ADD_FUND_TO_WALLET=>array('product_information','order_information','button_content','banner_image'),
            EmailTemplateKey::RESET_PASSWORD_VERIFICATION=>array('product_information','order_information','button_content','banner_image'),


        ];
        return $hiddenData[$templateName];
    }

    public function getAddData(string $userType,string $templateName,array|null $hideField,string $title, string $body):array
    {
        return [
            'template_name' => $templateName,
            'user_type' => $userType,
            'template_design_name' => $templateName,
            'title' => $title,
            'body' => $body,
            'hide_field' => $hideField ,
            'copyright_text' => translate('copyright_').date('Y').' '.getWebConfig('company_name').'. '.translate('all_right_reserved').'.',
            'footer_text' => translate('please_contact_us_for_any_queries').','.translate('_we_are_always_happy_to_help').'.',
        ];
    }
    public function getUpdateData(object $request,$template):array
    {
        $image = $request['image'] ? $this->update(dir:'email-template/', oldImage: $template['image'], format: 'webp',image:  $request->file('image')) : $template['image'];
        $logo = $request['logo'] ? $this->update(dir:'email-template/', oldImage: $template['logo'], format: 'webp',image:  $request->file('logo')) : $template['logo'];
        $icon = $request['icon'] ? $this->update(dir:'email-template/', oldImage: $template['logo'], format: 'webp',image:  $request->file('icon')) : $template['icon'];
        $bannerImage = $request['banner_image'] ? $this->update(dir: 'email-template/',oldImage:  $template['banner_image'], format: 'webp',image:  $request->file('banner_image')): $template['banner_image'];
        return [
            'title' => $request['title']['en'],
            'body' => $request['body']['en'],
            'banner_image' => $bannerImage,
            'image' => $icon,
            'logo' => $logo,
            'button_name' => $request['button_name']['en'] ?? null,
            'button_url' => $request['button_url'],
            'footer_text' => $request['footer_text']['en'],
            'copyright_text' => $request['copyright_text']['en'],
            'pages' => $request['social_media'] ? array_keys($request['pages']) :null,
            'social_media' =>$request['social_media'] ? array_keys($request['social_media']) : null,
            'button_content_status' => $request->get('button_content_status', 0),
            'product_information_status' => $request->get('product_information_status', 0),
            'order_information_status' => $request->get('order_information_status', 0),
        ];
    }

    public function getTitleData(string $userType,$templateName):string
    {
        $titleData =  [
            EmailTemplateKey::REGISTRATION=>translate('registration_complete'),
            EmailTemplateKey::REGISTRATION_VERIFICATION=>translate('registration_verification'),
            EmailTemplateKey::REGISTRATION_FROM_POS=>translate('registration_complete'),
            EmailTemplateKey::REGISTRATION_APPROVED=>translate('registration_approved'),
            EmailTemplateKey::REGISTRATION_DENIED=>translate('registration_denied'),
            EmailTemplateKey::ACCOUNT_ACTIVATION=>translate('account_activation'),
            EmailTemplateKey::ACCOUNT_SUSPENDED=>translate('account_suspended'),
            EmailTemplateKey::ACCOUNT_UNBLOCK=>translate('account_unblocked'),
            EmailTemplateKey::ACCOUNT_BLOCK=>translate('account_blocked'),
            EmailTemplateKey::DIGITAL_PRODUCT_DOWNLOAD=>translate('congratulations'),
            EmailTemplateKey::DIGITAL_PRODUCT_OTP=>translate('digital_product_download_otp_Verification'),
            EmailTemplateKey::ORDER_PLACE=>translate('order').' # '.'{orderId}'.translate('has_been_placed_successfully'),
            EmailTemplateKey::FORGET_PASSWORD=>translate('change_password_request'),
            EmailTemplateKey::ORDER_RECEIVED=>translate('new_order_received'),
            EmailTemplateKey::ADD_FUND_TO_WALLET=>translate('transaction_successful'),
            EmailTemplateKey::RESET_PASSWORD_VERIFICATION=>translate('otp_verification_for_password_reset'),
        ];
        return $titleData[$templateName];
    }
    public function getBodyData(string $userType,$templateName):string
    {
        $userType = match ($userType) {
            'admin' => '{adminName}',
            'customer' => '{userName}',
            'vendor' => '{vendorName}',
            'delivery-man' => '{deliveryManName}',
        };

        $bodyData = [
            EmailTemplateKey::REGISTRATION =>
                '<div><b>' . translate('hi') . ' ' . $userType . ',</b></div>
                 <div><br></div>
                 <div>' . str_replace(':company', getWebConfig('company_name'), translate('email_registration_body')) . '</div>
                 <div><br></div>
                 <div><font color="#0000ff"><a href="' . url('/') . '" target="_blank">' . url('/') . '</a></font></div>',
        
            EmailTemplateKey::REGISTRATION_VERIFICATION =>
                '<p><b>' . translate('hi') . ' ' . $userType . ',</b></p>
                 <p>' . translate('email_verification_code') . '</p>',
        
            EmailTemplateKey::REGISTRATION_FROM_POS =>
                '<p><b>' . translate('hi') . ' ' . $userType . ',</b></p>
                 <p>' . str_replace(':company', getWebConfig('company_name'), translate('email_registration_from_pos')) . '</p>',
        
            EmailTemplateKey::REGISTRATION_APPROVED =>
                '<div><b>' . translate('hi') . ' ' . $userType . ',</b></div>
                 <div><br></div>
                 <div>' . str_replace(':company', getWebConfig('company_name'), translate('email_registration_approved_body')) . '</div>
                 <div><br></div>
                 <div><font color="#0000ff"><a href="' . url('/') . '" target="_blank">' . url('/') . '</a></font></div>',
        
            EmailTemplateKey::REGISTRATION_DENIED =>
                '<div><b>' . translate('hi') . ' ' . $userType . ',</b></div>
                 <div><br></div>
                 <div>' . translate('email_registration_denied_body') . '</div>
                 <div><br></div>
                 <div><font color="#0000ff"><a href="' . url('/') . '" target="_blank">' . url('/') . '</a></font></div>',
        
            EmailTemplateKey::ACCOUNT_ACTIVATION =>
                '<div><b>' . translate('hi') . ' ' . $userType . ',</b></div>
                 <div><br></div>
                 <div>' . translate('email_account_activated') . '</div>
                 <div><br></div>
                 <div><font color="#0000ff"><a href="' . url('/') . '" target="_blank">' . url('/') . '</a></font></div>',
        
            EmailTemplateKey::ACCOUNT_SUSPENDED =>
                '<div><b>' . translate('hi') . ' ' . $userType . ',</b></div>
                 <div><br></div>
                 <div>' . translate('email_account_suspended_body') . '</div>
                 <div><br></div>
                 <div><font color="#0000ff"><a href="' . url('/') . '" target="_blank">' . url('/') . '</a></font></div>',
        
            EmailTemplateKey::ACCOUNT_UNBLOCK =>
                '<div><b>' . translate('hi') . ' ' . $userType . ',</b></div>
                 <div><br></div>
                 <div>' . translate('email_account_unblocked_body') . '</div>
                 <div><br></div>
                 <div><font color="#0000ff"><a href="' . url('/') . '" target="_blank">' . url('/') . '</a></font></div>',
        
            EmailTemplateKey::ACCOUNT_BLOCK =>
                '<div><b>' . translate('hi') . ' ' . $userType . ',</b></div>
                 <div><br></div>
                 <div>' . translate('email_account_blocked_body') . '</div>
                 <div><br></div>
                 <div><font color="#0000ff"><a href="' . url('/') . '" target="_blank">' . url('/') . '</a></font></div>',
        
            EmailTemplateKey::DIGITAL_PRODUCT_DOWNLOAD =>
                '<p>' . str_replace(':company', getWebConfig('company_name'), translate('email_digital_product_download')) . ' <b>{emailId}</b> ' . translate('and_order') . ' # {orderId}</p>',
        
            EmailTemplateKey::DIGITAL_PRODUCT_OTP =>
                '<p><b>' . translate('hi') . ' ' . $userType . ',</b></p>
                 <p>' . translate('email_digital_product_otp') . '</p>',
        
            EmailTemplateKey::ORDER_PLACE =>
                '<p><b>' . translate('hi') . ' ' . $userType . ',</b></p>
                 <p>' . translate('email_order_placed') . '</p>',
        
            EmailTemplateKey::FORGET_PASSWORD =>
                '<p><b>' . translate('hi') . ' ' . $userType . ',</b></p>
                 <p>' . translate('email_forgot_password') . '</p>',
        
            EmailTemplateKey::ORDER_RECEIVED =>
                '<p><b>' . translate('hi') . ' ' . $userType . ',</b></p>
                 <p>' . translate('email_order_received_notice') . '</p>',
        
            EmailTemplateKey::ADD_FUND_TO_WALLET =>
                '<div style="text-align: center;">' . translate('email_wallet_credit') . '</div><div style="text-align: center;"><br></div>',
        
            EmailTemplateKey::RESET_PASSWORD_VERIFICATION =>
                '<p><b>' . translate('hi') . ' ' . $userType . ',</b></p>
                 <p>' . translate('email_password_reset_otp') . '</p>',
        ];
              
        return $bodyData[$templateName];
    }
}
