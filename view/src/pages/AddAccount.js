import React from 'react';
import { useState,useEffect } from "react";
import { Button,Modal, Container, Tab, Tabs, Row, Col } from 'react-bootstrap';
export default function AddAccount(){
    return (
        <div className="container-fluid">
            <form>
                <h3 >註冊帳號</h3>
                <div className="form-group row">
                    <label htmlFor="name" className="col-form-label col-sm-auto my-1">姓名</label>
                    <div className="col-sm-auto">
                        <input type="text" id="name" className="form-control my-1" placeholder="ex.王大明" />
                    </div>
                </div>
                <div className="form-group row">
                    <label htmlFor="email" className="col-form-label col-sm-auto my-1">電子信箱</label>
                    <div className="col-sm-auto">
                        <input type="text" id="email" className="form-control my-1" placeholder="ex.12345@gmail.com" />
                    </div>
                </div>
                <div className="form-group row">
                    <label htmlFor="password" className="col-form-label col-sm-auto my-1">密碼</label>
                    <div className="col-sm-auto">
                        <input type="password" id="password" className="form-control my-1" />
                    </div>
                </div>
                <div className="form-group row">
                    <label htmlFor="surePassword" className="col-form-label col-sm-auto my-1">確認密碼</label>
                    <div className="col-sm-auto">
                        <input type="password" id="surepassword" className="form-control my-1" />
                    </div>
                </div>
                <div className="form-group row">
                    <label htmlFor="phone" className="col-form-label col-sm-auto my-1">手機號碼</label>
                    <div className="col-sm-auto">
                        <input type="text" id="phone" className="form-control my-1" placeholder="0912345678" />
                    </div>
                </div>
                <div class="form-group col-auto">
                    <button type="button" class=" btn btn-primary my-1">立即註冊</button>
                </div>
            </form>
        </div>
    )
}
