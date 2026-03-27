import * as qrcode from "qrcode";

window.qrcode = qrcode;
window.QRCode = window.QRCode || qrcode; // allow compatibility with existing code expecting QRCode
