@extends('layouts.app')
@section('title', ($userAddress->id?'修改':'新增').'收货地址')

@section('content')
    <div class="row">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h2 class="text-center">
                        {{ $userAddress->id?'修改':'新增' }}收货地址
                    </h2>
                </div>
                <div class="panel-body">
                    <!-- 输出后端报错 -->
                    @if(count($errors) > 0)
                        <div class="alert alert-danger">
                            <h4>有错误发生:</h4>
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li><i class="glyphicon glyphicon-remove">{{$error}}</i> </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <!-- 输出后端报错 -->
                        <!-- inline-template 代表通过内联方式引入组件 -->
                        <user-addresses-create-and-edit inline-template>
                    @if($userAddress->id)

                       <form class="form-horizontal" role="form" method="post" action="{{ route('user_addresses.update', $userAddress->id) }}">
                         {{ method_field('PATCH') }}
                    @else
                    <form class="form-horizontal" role="form" method="post" action="{{ route('user_addresses.store') }}">
                    @endif
                        {{ csrf_field() }}
                        <!-- inline-template 代表通过内联方式引入组件 -->
                        <select-district :init-value="{{ json_encode([$userAddress->province, $userAddress->city, $userAddress->district]) }}"  @change="onDistrictChanged" inline-template>
                            <div class="form-group">
                                <label class="control-label col-sm-2">省市区</label>
                                <div class="col-sm-3">
                                    <select class="form-control" v-model="provinceId">
                                        <option value="">选择省</option>
                                        <option v-for="(name, id) in provinces" :value="id">@{{ name }}</option>
                                    </select>
                                </div>
                                <div class="col-sm-3">
                                    <select class="form-control" v-model="cityId">
                                        <option value="">选择市</option>
                                        <option v-for="(name, id) in cities" :value="id">@{{ name }}</option>
                                    </select>
                                </div>
                                <div class="col-sm-3">
                                    <select class="form-control" v-model="districtId">
                                        <option value="">选择区</option>
                                        <option v-for="(name, id) in districts" :value="id">@{{ name }}</option>
                                    </select>
                                </div>
                            </div>
                        </select-district>

                        <!-- 插入了 3 个隐藏的字段 -->
                        <!-- 通过 v-model 与 user-addresses-create-and-edit 组件里的值关联起来 -->
                        <!-- 当组件中的值变化时，这里的值也会跟着变 -->

                        <input type="hidden" name="province" v-model="province">
                        <input type="hidden" name="city" v-model="city">
                        <input type="hidden" name="district" v-model="district">

                        <div class="form-group">
                            <label class="control-label col-sm-2">详细地址</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="address" value="{{ old('address', $userAddress->address) }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-sm-2">邮政编码</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="zip" value="{{ old('zip', $userAddress->zip) }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-sm-2">电话</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="contact_phone" value="{{ old('contact_phone', $userAddress->contact_phone) }}">
                            </div>
                        </div>


                        <div class="form-group">
                            <label class="control-label col-sm-2">姓名</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="contact_name" value="{{ old('contact_name', $userAddress->contact_name) }}">
                            </div>
                        </div>

                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-primary">确定</button>
                        </div>
                    </form>
                            </user-addresses-create-and-edit>
                </div>
            </div>
        </div>
    </div>
@endsection