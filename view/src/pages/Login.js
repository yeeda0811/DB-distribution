import React from 'react';
import { useState, useEffect } from "react";
import { Button, Card, Modal, Container, Tab, Tabs, Row, Col, Form } from 'react-bootstrap';
function Login() {
    const [account, setAccount] = useState("");
    const [password, setPassword] = useState("");
    const [show, setShow] = useState(false);
    const handleClose = () => setShow(false);
    const handleShow = () => setShow(true);

    return (
        <>

            <Button variant="primary" onClick={handleShow}>
                登入
            </Button>
            <Modal show={show} onHide={handleClose}>
                <Modal.Header closeButton>
                    <Modal.Title>登入</Modal.Title>
                </Modal.Header>
                <Modal.Body>
                    <div class="form-group row">
                        {/* <Label >帳號</Label>  */}
                        <label class="col-form-label col-sm-auto my-2">帳號 : </label>
                        <Col xs={8} md={6}>
                            <Form.Control type="text" onChange={(e) => { setAccount(e.target.value) }} />
                        </Col>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-sm-auto my-2">密碼 : </label>
                        <Col xs={8} md={6}>
                            <Form.Control type="password" onChange={(e) => { setPassword(e.target.value) }} />
                        </Col>
                    </div>
                    <div class="form-group text-center">
                        <a href="" src="/drawPic?">忘記密碼</a>
                    </div>
                </Modal.Body>
                <Modal.Footer>
                    <Button variant="secondary" onClick={handleClose}>
                        取消
                    </Button>
                    <Button variant="primary" onClick={handleClose}>
                        登入
                    </Button>
                </Modal.Footer>
            </Modal >
        </>
    );
}

export default Login;