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
                                <label for="vat_percent" class="form-label">Ставка НДС</label>
                                <input type="text" name="vat_percent" id="vat_percent" class="form-control" value="{{$seller?->shop->vat_percent}}">
                            </div>

                            <div class="form-group mb-3">
                                <label for="organization_oked" class="form-label">ОКЭД</label>
                                <input type="text" name="organization_oked" id="organization_oked" class="form-control" value="{{$seller?->shop->organization_oked}}">
                            </div>

                            <div class="form-group mb-3">
                                <label for="latitude" class="form-label">Широта</label>
                                <input type="text" name="latitude" id="latitude" class="form-control" value="{{$seller?->shop->latitude}}">
                            </div>

                            <div class="form-group mb-3">
                                <label for="longitude" class="form-label">Долгота</label>
                                <input type="text" name="longitude" id="longitude" class="form-control" value="{{$seller?->shop->longitude}}">
                            </div>
                        </div>

                        {{-- Карта для выбора координат --}}
                        @php($default_location = getWebConfig(name: 'default_location'))
                        @if(getWebConfig('map_api_status') == 1)
                        <div class="form-group">
                            <div class="map-area-alert-border">
                            <input id="pac-input-merchant"
                                    class="controls rounded __inline-46 location-search-input-field"
                                    type="text"
                                    placeholder="{{translate('search_here')}}"
                                    title="{{translate('search_your_location_here')}}" />
                            <div id="location_map_canvas_merchant" style="height: 220px; border-radius: 8px;"></div>
                            <button type="button" class="btn btn--primary mt-3 w-100" onclick="locateMeMerchant()">
                                📍 {{ translate('locate_me') }}
                            </button>
                            </div>
                        </div>
                        @endif

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
@if(getWebConfig('map_api_status') == 1)
  <script
    src="https://maps.googleapis.com/maps/api/js?key={{ getWebConfig('map_api_key') }}&callback=callBackMerchantMap&loading=async&libraries=places&v=3.56"
    defer>
  </script>
  <script>
    "use strict";

    async function initMerchantMap() {
      // стартовая точка
      const startLat = {{ $default_location ? $default_location['lat'] : '41.3111' }};   // Tashkent by default
      const startLng = {{ $default_location ? $default_location['lng'] : '69.2797' }};

      const latInput = document.getElementById('latitude');
      const lngInput = document.getElementById('longitude');
      const addrArea  = document.getElementById('shop_address'); // опционально, если есть поле адреса

      // если уже есть значения в инпутах — используем их как старт
      const center = {
        lat: parseFloat(latInput?.value || startLat) || startLat,
        lng: parseFloat(lngInput?.value || startLng) || startLng,
      };

      const { Map } = await google.maps.importLibrary("maps");
      const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");
      const mapEl = document.getElementById('location_map_canvas_merchant');

      const map = new Map(mapEl, {
        center,
        zoom: 13,
        mapId: 'roadmap'
      });

      const marker = new AdvancedMarkerElement({
        map,
        position: center
      });

      // в глобальную область — пригодится в locateMeMerchant()
      window.__merchantMap = map;
      window.__merchantMarker = marker;

      const geocoder = new google.maps.Geocoder();

      // клик по карте — ставим маркер и пишем координаты
      map.addListener('click', (e) => {
        const coords = e.latLng.toJSON();
        marker.position = coords;
        map.panTo(e.latLng);

        latInput.value = coords.lat;
        lngInput.value = coords.lng;

        // обратное геокодирование (опционально — в #shop_address)
        if (addrArea) {
          geocoder.geocode({ location: coords }, (results, status) => {
            if (status === "OK" && results?.[0]) {
              addrArea.value = buildAddressString(results[0].address_components);
            }
          });
        }
      });

      // поиск по places
      const searchInput = document.getElementById('pac-input-merchant');
      const searchBox = new google.maps.places.SearchBox(searchInput);
      map.controls[google.maps.ControlPosition.TOP_CENTER].push(searchInput);

      map.addListener("bounds_changed", () => {
        searchBox.setBounds(map.getBounds());
      });

      let searchMarkers = [];
      searchBox.addListener("places_changed", () => {
        const places = searchBox.getPlaces();
        if (!places || !places.length) return;

        // убрать старые маркеры
        searchMarkers.forEach(m => m.setMap && m.setMap(null));
        searchMarkers = [];

        const bounds = new google.maps.LatLngBounds();

        places.forEach((place) => {
          if (!place.geometry || !place.geometry.location) return;

          const m = new AdvancedMarkerElement({
            map,
            position: place.geometry.location,
            title: place.name
          });

          // клик по маркеру из поиска — зафиксировать координаты в инпуты
          m.addListener?.('gmp-click', () => {
            const p = m.position; // google.maps.LatLng|object
            const lat = typeof p.lat === 'function' ? p.lat() : p.lat;
            const lng = typeof p.lng === 'function' ? p.lng() : p.lng;
            latInput.value = lat;
            lngInput.value = lng;
            marker.position = { lat, lng };
          });

          searchMarkers.push(m);

          if (place.geometry.viewport) bounds.union(place.geometry.viewport);
          else bounds.extend(place.geometry.location);
        });

        map.fitBounds(bounds);
      });

      // вспомогалки
      function get(comp, type) {
        return comp.find(c => c.types.includes(type))?.long_name || '';
      }
      function buildAddressStringFromComponents(components){
        const region       = get(components, 'administrative_area_level_1');
        const district     = get(components, 'administrative_area_level_2') || get(components, 'locality');
        const street       = get(components, 'route');
        const streetNumber = get(components, 'street_number');
        const parts = [
          region,
          district,
          [streetNumber, street].filter(Boolean).join(' ')
        ];
        return parts.filter(Boolean).join(', ');
      }
      window.buildAddressString = buildAddressStringFromComponents; // экспорт для клик/геокодера
    }

    // кнопка «Найти меня»
    function locateMeMerchant() {
      if (!navigator.geolocation) {
        alert("{{ translate('your_browser_does_not_support_geolocation') }}");
        return;
      }
      const map = window.__merchantMap;
      const marker = window.__merchantMarker;
      const latInput = document.getElementById('latitude');
      const lngInput = document.getElementById('longitude');
      const addrArea  = document.getElementById('shop_address');

      navigator.geolocation.getCurrentPosition((pos) => {
        const coords = { lat: pos.coords.latitude, lng: pos.coords.longitude };
        marker.position = coords;
        map.setCenter(coords);
        map.setZoom(15);

        latInput.value = coords.lat;
        lngInput.value = coords.lng;

        const geocoder = new google.maps.Geocoder();
        geocoder.geocode({ location: coords }, (results, status) => {
          if (status === "OK" && results?.[0] && addrArea) {
            addrArea.value = buildAddressString(results[0].address_components);
          }
        });
      }, (err) => {
        alert("{{ translate('geolocation_error') }}: " + err.message);
      });
    }

    // колбэк из script src
    function callBackMerchantMap(){
      initMerchantMap();
    }
  </script>
@endif
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
