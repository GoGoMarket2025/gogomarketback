<div class="second-el d--none">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <h3 class="mb-4">{{translate('create_an_account')}}</h3>
                        <div class="border p-3 p-xl-4 rounded">
                            <h4 class="mb-3">Информация о руководители</h4>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group mb-4">
                                        <label  for="f_name">{{translate('first_name')}} <span class="text-danger">*</span></label>
                                        <input class="form-control" type="text" name="f_name" placeholder="{{translate('ex').': John'}}" required>
                                    </div>
                                    <div class="form-group mb-4">
                                        <label  for="l_name">{{translate('last_name')}} <span class="text-danger">*</span></label>
                                        <input class="form-control" type="text" name="l_name" placeholder="{{translate('ex').': Doe'}}" required>
                                    </div>

                                    <div class="form-group mb-4">
                                        <label  for="passport_serial">{{translate('passport_serial')}} <span class="text-danger">*</span></label>
                                        <input class="form-control" type="text" name="passport_serial" placeholder="AA" required>
                                    </div>

                                    <div class="form-group mb-4">
                                        <label  for="passport_number">{{translate('passport_number')}} <span class="text-danger">*</span></label>
                                        <input class="form-control" type="text" name="passport_number" placeholder="123456" required>
                                    </div>

                                    <div class="form-group mb-4">
                                        <label  for="passport_issue_name">{{translate('passport_issue_name')}} <span class="text-danger">*</span></label>
                                        <input class="form-control" type="text" name="passport_issue_name" placeholder="Кем выдан" required>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="d-flex flex-column gap-3 align-items-center">
                                        <div class="upload-file">
                                            <input type="file" class="upload-file__input" name="image" accept="image/*" required>
                                            <div class="upload-file__img">
                                                <div class="temp-img-box">
                                                    <div class="d-flex align-items-center flex-column gap-2">
                                                        <i class="tio-upload fs-30"></i>
                                                        <div class="fs-12 text-muted text-non-capitalize">{{translate('upload_file')}}</div>
                                                    </div>
                                                </div>
                                                <img src="#" class="dark-support img-fit-contain border" alt="" hidden>
                                            </div>
                                        </div>

                                        <div class="d-flex flex-column gap-1 upload-img-content text-center">
                                            <h6 class="text-uppercase mb-1 fs-14">{{translate('vendor_image')}}</h6>
                                            <div class="text-muted text-non-capitalize fs-12">{{translate('image_ratio').' '.'1:1'}}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border p-3 p-xl-4 rounded mt-4">
                            <h4 class="mb-3 text-non-capitalize">{{translate('shop_information')}}</h4>

                            <div class="form-group mb-4">
                                <label for="store_name" class="text-non-capitalize">{{translate('shop_Name')}} <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" id="shop_name"  name="shop_name" placeholder="{{translate('Ex: XYZ store')}}" required>
                            </div>
                            <div class="form-group mb-4">
                                <label for="store_address" class="text-non-capitalize">{{translate('shop_address')}} <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="shop_address" id="shop_address" rows="4" placeholder="{{translate('shop_address')}}" required></textarea>
                            </div>

                            <!-- ORGANIZATION TYPE (styled like payment radios) -->
                            <div class="form-group mb-4" id="orgTypeGroup">
                                <label for="org_type_ip" class="text-non-capitalize">
                                    {{ translate('organization_type') }} <span class="text-danger">*</span>
                                </label>

                                <div class="d-flex flex-wrap gap-3">
                                    <!-- ИП -->
                                    <label class="m-0">
                                    <span class="btn btn-block click-if-alone d-flex gap-2 align-items-center cursor-pointer p-3 org-card">
                                        <input type="radio"
                                            id="org_type_ip"
                                            name="organization_type"
                                            value="1"
                                            class="custom-radio"
                                            required>
                                        <img width="20" src="{{ theme_asset(path: 'public/assets/front-end/img/icons/user-check.png') }}" alt="">
                                        <span class="fs-12">{{ translate('ИП') }}</span>
                                    </span>
                                    </label>

                                    <!-- ООО -->
                                    <label class="m-0">
                                    <span class="btn btn-block click-if-alone d-flex gap-2 align-items-center cursor-pointer p-3 org-card">
                                        <input type="radio"
                                            id="org_type_ooo"
                                            name="organization_type"
                                            value="2"
                                            class="custom-radio"
                                            required>
                                        <img width="20" src="{{ theme_asset(path: 'public/assets/front-end/img/icons/building.png') }}" alt="">
                                        <span class="fs-12">{{ translate('ООО') }}</span>
                                    </span>
                                    </label>
                                </div>
                            </div>


                            <div class="form-group mb-4">
                                <label  for="organization_name">{{translate('organization_name')}} <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="organization_name" placeholder="123456" required>
                            </div>

                            <div class="form-group mb-4">
                                <label  for="organization_oked">{{translate('organization_oked')}} <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="organization_oked" placeholder="123456" required>
                            </div>

                            <div class="form-group mb-4">
                                <label  for="bank_account_number">{{translate('bank_account_number')}} <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="bank_account_number" placeholder="123456" required>
                            </div>

                            <div class="form-group mb-4">
                                <label  for="bank_name">{{translate('bank_name')}} <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="bank_name" placeholder="123456" required>
                            </div>

                            <div class="form-group mb-4">
                                <label  for="bank_mfo_code">{{translate('bank_mfo_code')}} <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="bank_mfo_code" placeholder="123456" required>
                            </div>

                            <div class="form-group mb-4">
                                <label  for="identification_number">{{translate('identification_number')}} <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="identification_number" placeholder="123456" required>
                            </div>

                            <div class="form-group mb-4">
                                <label  for="vat_percent">{{translate('vat_percent')}} <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="vat_percent" placeholder="123456" required>
                            </div>

                            <div class="form-group mb-4">
                                <label  for="latitude">{{translate('latitude')}} <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="latitude" placeholder="123456" required>
                            </div>

                            <div class="form-group mb-4">
                                <label  for="longitude">{{translate('longitude')}} <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="longitude" placeholder="123456" required>
                            </div>

                            <div class="d-flex justify-content-between border p-3 p-xl-4 rounded mb-4">
                                <div class="d-flex flex-column gap-3 align-items-center">
                                    <div class="upload-file">
                                        <input type="file" class="upload-file__input" name="logo" accept="image/*" required>
                                        <div class="upload-file__img">
                                            <div class="temp-img-box">
                                                <div class="d-flex align-items-center flex-column gap-2">
                                                    <i class="tio-upload fs-30"></i>
                                                    <div class="fs-12 text-muted text-non-capitalize">{{translate('upload_file')}}</div>
                                                </div>
                                            </div>
                                            <img src="#" class="dark-support img-fit-contain border" alt="" hidden>
                                        </div>
                                    </div>

                                    <div class="d-flex flex-column gap-1 upload-img-content text-center">
                                        <h6 class="text-uppercase mb-1 fs-14">{{translate('upload_logo')}}</h6>
                                        <div class="text-muted text-non-capitalize fs-12">{{translate('image_ratio').' '.'1:1'}}</div>
                                        <div class="text-muted text-non-capitalize fs-12">{{translate('Image Size : Max 2 MB')}}</div>
                                    </div>
                                </div>
                                <div class="d-flex flex-column gap-3 align-items-center">
                                    <div class="upload-file">
                                        <input type="file" class="upload-file__input" name="banner" accept="image/*" required>
                                        <div class="upload-file__img style--two">
                                            <div class="temp-img-box">
                                                <div class="d-flex align-items-center flex-column gap-2">
                                                    <i class="tio-upload fs-30"></i>
                                                    <div class="fs-12 text-muted text-non-capitalize">{{translate('upload_file')}}</div>
                                                </div>
                                            </div>
                                            <img src="#" class="dark-support img-fit-contain border" alt="" hidden>
                                        </div>
                                    </div>

                                    <div class="d-flex flex-column gap-1 upload-img-content text-center">
                                        <h6 class="text-uppercase mb-1 fs-14">{{translate('upload_banner')}}</h6>
                                        <div class="text-muted text-non-capitalize fs-12">{{translate('image_ratio').' '.'2:1'}}</div>
                                        <div class="text-muted text-non-capitalize fs-12">{{translate('Image Size : Max 2 MB')}}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @php($recaptcha = getWebConfig(name: 'recaptcha'))
                        @if(isset($recaptcha) && $recaptcha['status'] == 1)
                            <div id="recaptcha-element-vendor-register" class="w-100 pt-2" data-type="image"></div>
                        @else
                            <div class="mt-2">
                                <div class="row py-2">
                                    <div class="col-6 pr-0">
                                        <input type="text" class="form-control __h-40" name="default_recaptcha_id_seller_regi" id="default-recaptcha-id-vendor-register" value=""
                                               placeholder="{{translate('enter_captcha_value')}}" autocomplete="off" required>
                                    </div>
                                    <div class="col-6 input-icons mb-2 w-100 rounded bg-white">
                                    <span class="d-flex align-items-center align-items-center get-vendor-regi-recaptcha-verify"
                                          data-link="{{ route('vendor.auth.recaptcha', ['tmp'=>':dummy-id']) }}">
                                        <img src="{{ route('vendor.auth.recaptcha', ['tmp'=>1]).'?captcha_session_id=vendorRecaptchaSessionKey' }}" alt="" class="rounded __h-40" id="default_recaptcha_id">
                                        <i class="tio-refresh position-relative cursor-pointer p-2"></i>
                                    </span>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class="d-flex justify-content-start mt-2">
                            <label class="custom-checkbox align-items-center">
                                <input type="checkbox" class="" id="terms-checkbox" >
                                <span class="form-check-label">{{ translate('i_agree_with_the') }} <a
                                        href="{{ route('business-page.view', ['slug' => 'terms-and-conditions'])}}" target="_blank" class="text-underline color-bs-primary-force">
                                        {{ translate('terms_&_conditions') }}
                                    </a>
                                </span>
                            </label>
                        </div>
                        <div class="d-flex justify-content-end mb-2 gap-2">
                            <button type="button" class="btn btn-secondary back-to-main-page"> {{translate('back')}} </button>
                            <button type="button" class="btn btn--primary"  id="vendor-apply-submit" disabled="disabled"> {{translate('submit')}} </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
  /* Карточка под radio — как на оплате */
  #orgTypeGroup .org-card{
    border:1px solid #e9ecef;
    border-radius:.5rem;
    transition:border-color .2s,background-color .2s,box-shadow .2s;
  }
  #orgTypeGroup .org-card:hover{
    border-color:#cfd6dc;
  }
  /* Выбранное состояние — чистый CSS через :has (работает в современных браузерах) */
  #orgTypeGroup .org-card:has(> input.custom-radio:checked){
    border-color:#0d6efd;
    background:#f0f7ff;
    box-shadow:0 0 0 .2rem rgba(13,110,253,.125);
  }
  /* Чуть выровнять видимый кружок из payment.css */
  #orgTypeGroup .custom-radio{
    margin-right:.25rem;
  }
</style>

<script>
  // Подсветка активной «карточки» как на платежной странице
  document.addEventListener('DOMContentLoaded', function () {
    const cards = document.querySelectorAll('#orgTypeGroup .org-card');
    const sync = () => cards.forEach(c => {
      const input = c.querySelector('.custom-radio');
      c.classList.toggle('active', input.checked);
    });
    cards.forEach(c => c.querySelector('.custom-radio').addEventListener('change', sync));
    sync(); // на случай server-side checked
  });
</script>
<style>
  /* Fallback-стили для класса .active (если включишь JS выше) */
  #orgTypeGroup .org-card.active{
    border-color:#0d6efd;
    background:#f0f7ff;
    box-shadow:0 0 0 .2rem rgba(13,110,253,.125);
  }
</style>