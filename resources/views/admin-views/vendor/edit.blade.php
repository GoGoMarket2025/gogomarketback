@extends('layouts.admin.app')

@section('title', $seller?->shop->name ?? translate("shop_Name"))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="h1 mb-0 text-non-capitalize d-flex align-items-center gap-2">
                <img src="{{dynamicAsset(path: 'public/assets/back-end/img/add-new-seller.png')}}" alt="">
                {{translate('edit_vendor_details')}}
            </h2>
        </div>

        <div class="card card-top-bg-element mb-5">
            <div class="card-body">
                <form action="{{ route('admin.vendors.update-shop', $seller['id']) }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="d-flex flex-wrap gap-3 justify-content-between">
                        <div class="media flex-column flex-sm-row gap-3">
                            <div class="avatar-upload">
                                <div class="avatar-edit">
                                    <input type="file" id="shopImageUpload" name="image" accept=".png, .jpg, .jpeg" />
                                    <label for="shopImageUpload"></label>
                                </div>
                                <div class="avatar-preview">
                                    <div id="shopImagePreview" style="background-image: url('{{ getStorageImages(path: $seller?->shop->image_full_url, type: 'backend-basic') }}');">
                                    </div>
                                </div>
                            </div>
                            <div class="media-body">
                                <div class="d-block">
                                    <h2 class="mb-2 pb-1">{{ $seller->shop? $seller->shop->name : translate("shop_Name")." : ".translate("update_Please") }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row gy-3">
                        <div class="col-md-6">
                            <h4 class="mb-3 text-non-capitalize">{{translate('shop_information')}}</h4>
                            <div class="form-group mb-3">
                                <label for="name" class="form-label">{{translate('shop_name')}}</label>
                                <input type="text" name="name" id="name" class="form-control" value="{{$seller?->shop->name}}" required>
                            </div>

                            <div class="form-group mb-3">
                                <label for="contact" class="form-label">{{translate('phone')}}</label>
                                <input type="text" name="contact" id="contact" class="form-control" value="{{$seller?->shop->contact}}">
                            </div>

                            <div class="form-group mb-3">
                                <label for="address" class="form-label">{{translate('address')}}</label>
                                <textarea name="address" id="address" class="form-control" rows="3">{{$seller?->shop->address}}</textarea>
                            </div>

                            <div class="form-group mb-3">
                                <label for="identification_number" class="form-label">ИНН / ПИНФЛ</label>
                                <input type="text" name="identification_number" id="identification_number" class="form-control" value="{{$seller?->shop->identification_number}}">
                            </div>

                            <div class="form-group mb-3">
                                <label for="organization_type" class="form-label">Тип</label>
                                <select name="organization_type" id="organization_type" class="form-control">
                                    <option value="">- Выберите тип -</option>
                                    <option value="1" {{$seller?->shop?->organization_type == 1 ? 'selected' : ''}}>{{ translate('ИП') }}</option>
                                    <option value="2" {{$seller?->shop?->organization_type == 2 ? 'selected' : ''}}>{{ translate('ООО') }}</option>
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label for="organization_name" class="form-label">Организация</label>
                                <input type="text" name="organization_name" id="organization_name" class="form-control" value="{{$seller?->shop->organization_name}}">
                            </div>

                            <div class="form-group mb-3">
                                <label for="organization_oked" class="form-label">ОКЭД</label>
                                <input type="text" name="organization_oked" id="organization_oked" class="form-control" value="{{$seller?->shop->organization_oked}}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h4 class="mb-3 text-non-capitalize">{{translate('bank_information')}}</h4>
                            <div class="form-group mb-3">
                                <label for="bank_account_number" class="form-label">Счет в банке</label>
                                <input type="text" name="bank_account_number" id="bank_account_number" class="form-control" value="{{$seller?->shop->bank_account_number}}">
                            </div>

                            <div class="form-group mb-3">
                                <label for="bank_name" class="form-label">Название банка</label>
                                <input type="text" name="bank_name" id="bank_name" class="form-control" value="{{$seller?->shop->bank_name}}">
                            </div>

                            <div class="form-group mb-3">
                                <label for="bank_mfo_code" class="form-label">МФО банка</label>
                                <input type="text" name="bank_mfo_code" id="bank_mfo_code" class="form-control" value="{{$seller?->shop->bank_mfo_code}}">
                            </div>

                            <h4 class="mb-3 mt-4 text-non-capitalize">{{translate('passport_information')}}</h4>
                            <div class="form-group mb-3">
                                <label for="passport_serial" class="form-label">Серия паспорта</label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="text" name="passport_serial" id="passport_serial" class="form-control" value="{{$seller?->shop->passport_serial}}" placeholder="Серия">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" name="passport_number" id="passport_number" class="form-control" value="{{$seller?->shop->passport_number}}" placeholder="Номер">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label for="passport_issue_name" class="form-label">Выдан</label>
                                <input type="text" name="passport_issue_name" id="passport_issue_name" class="form-control" value="{{$seller?->shop->passport_issue_name}}">
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-3 mt-4">
                        <a href="{{ route('admin.vendors.view', $seller['id']) }}" class="btn btn-secondary">{{translate('cancel')}}</a>
                        <button type="submit" class="btn btn-primary">{{translate('save')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script')
<script>
    $(document).ready(function(){
        // Image preview
        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#shopImagePreview').css('background-image', 'url(' + e.target.result + ')');
                    $('#shopImagePreview').hide();
                    $('#shopImagePreview').fadeIn(650);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        $("#shopImageUpload").change(function() {
            readURL(this);
        });
    });
</script>
@endpush

@push('style')
<style>
    .avatar-upload {
        position: relative;
        max-width: 170px;
        margin-bottom: 20px;
    }
    .avatar-upload .avatar-edit {
        position: absolute;
        right: 10px;
        z-index: 1;
        top: 10px;
    }
    .avatar-upload .avatar-edit input {
        display: none;
    }
    .avatar-upload .avatar-edit label {
        display: inline-block;
        width: 34px;
        height: 34px;
        margin-bottom: 0;
        border-radius: 100%;
        background: #FFFFFF;
        border: 1px solid transparent;
        box-shadow: 0px 2px 4px 0px rgba(0, 0, 0, 0.12);
        cursor: pointer;
        font-weight: normal;
        transition: all .2s ease-in-out;
    }
    .avatar-upload .avatar-edit label:hover {
        background: #f1f1f1;
        border-color: #d6d6d6;
    }
    .avatar-upload .avatar-edit label:after {
        content: "\f040";
        font-family: 'FontAwesome';
        color: #757575;
        position: absolute;
        top: 8px;
        left: 0;
        right: 0;
        text-align: center;
        margin: auto;
    }
    .avatar-upload .avatar-preview {
        width: 170px;
        height: 170px;
        position: relative;
        border-radius: 100%;
        border: 6px solid #F8F8F8;
        box-shadow: 0px 2px 4px 0px rgba(0, 0, 0, 0.1);
    }
    .avatar-upload .avatar-preview > div {
        width: 100%;
        height: 100%;
        border-radius: 100%;
        background-size: cover;
        background-repeat: no-repeat;
        background-position: center;
    }
</style>
@endpush
