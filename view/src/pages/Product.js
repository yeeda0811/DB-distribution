import React from 'react';
// import './index.css';
import { useState,useEffect } from "react";
import axios from 'axios';
// import ReactDOM from "react-dom";
import InputNumber from 'react-input-number';
import { Button, Card, Container, Tab, Tabs, Row, Col ,Form} from 'react-bootstrap';

export default function Product() {
    const [num, setNum] = useState(1);
    const [get, getItems] = useState(null);
    const URL= window.location.href.split("/");
    const item_id=URL[URL.length-1];
    console.log(item_id);
    const baseURL = `/admin/productmanage/item/${item_id}`;

    useEffect(() => {
        axios.get(baseURL).then((response) => {
            console.log(response.data);
            getItems(response.data);
          });
    },[]);
    if (!get) return null;

    return (
        <div class="container-fluid">
            <div class="row">
                <Col sm={6}>圖片</Col>
                <Col sm={6}>
                    <h2>{get[0].item_name}</h2>
                    <h4>價錢:NT${get[0].price}</h4>
                    <div class="row">
                        <div class="form-group col-auto my-3">數量：</div>
                        <div class="form-group col-auto my-2">
                            < Form.Control
                                type="number"
                                min={1}
                                max={20}
                                step={1}
                                value={num}
                                onChange={e => setNum(e.target.value)}
                            />
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-auto">
                            <button type="button" class=" btn btn-outline-info">立即購買</button>
                        </div>
                        <div class="form-group col-auto">
                            <button type="button" class=" btn btn-outline-success ">加入購物車</button>
                        </div>

                    </div>

                </Col>
            </div>
                <div class="align-text-center mx-4">
                {get[0].describe}
                </div>
        </div>
    )
}


// export default Product;