<?php
echo '<div style="background: #fafafa;background-color:#f5f5f5;background-position:center top;background-repeat:repeat-x;font-family:Roboto,Helvetica Neue,Helvetica,Arial,sans-serif;font-size:16px;margin:0;padding:0 10px;">
    <table align="center" style="width:100%" border="0" cellspacing="0" cellpadding="0">
        <tbody>
            <tr>
                <td align="center" style="padding-top:30px;padding-bottom:30px;">
                    <table align="center" style="max-width:475px;width:100%" border="0" cellspacing="0" cellpadding="0">
                        <tbody>
                            <tr>
                                <td style="padding-top:10px">
                                    <table align="center" border="0" cellpadding="0" cellspacing="0" style="margin:0 auto;width:100%">
                                        <tbody>
                                            <tr>
                                                <td style="padding-bottom:30px;font-size:0px;line-height:0px">&nbsp;</td>
                                            </tr>
                                            <tr>
                                                <td bgcolor="#FFFFFF">
                                                    <tr>
                                                        <td bgcolor="#FFFFFF" style="text-align:center;padding: 20px;color: #d2383d;"><span style="font-size: 90px;font-weight: bold;">'.$trans_credit.'</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td bgcolor="#FFFFFF" style="color:#fff;background:#1a73e8;text-align:center;padding:15px"><span style="font-size:25px;font-weight: bold;">CREDIT BALANCE</span></td>
                                                    </tr>
                                                </td>
                                            </tr>
                                            <tr bgcolor="#FFFFFF">
                                                <td style="padding-top:25px;padding-right:20px;padding-left:20px;padding-bottom:20px;border-right:1px solid #dddddd;border-left:1px solid #dddddd;border-bottom:1px solid #dddddd">
                                                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                        <tbody>
                                                            <tr></tr>
                                                        </tbody>
                                                    </table>
                                                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                        <tbody>
                                                            <tr>
                                                                <td align="initial" style="padding-top:5px;padding-bottom:10px;font-weight:normal;font-size:16px;line-height:26px;color:#6f8996">Dear '.$username.',
                                                                    <br>
                                                                    <br>We are writing this email to remind you, that your account balance has reached below the set level.
                                                                    <br />
                                                                    <br />We recommend to purchase the credits immediately, to enjoy continued order notifications.
                                                                    <br />
                                                                    <br />Remember the more you purchase the more you save <span style="font-size:30px;color:orange;">&#9787;	</span></td>
                                                            </tr>
                                                            
                                                        </tbody>
                                                    </table>
                                                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                        <tbody>
                                                            <tr>
                                                                <td align="center" style="padding-top:10px;padding-bottom:17px">
                                                                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td align="center">
                                                                                    <center><a href="https://www.newsletters.lk" style="background:#03a9f4;border-radius:3px;color:#ffffff;display:block;font-family:Roboto,Helvetica Neue,Helvetica,Arial,sans-serif;font-weight:normal;letter-spacing:1px;padding:14px 8px;text-decoration:none" target="_blank">BUY CREDIT </a></center>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td style="border-top: 1px solid #ccc;line-height: 0"></td>
                            </tr>
                            <tr>
                                <td bgcolor="#fafafa" align="center" style="font-family:Roboto,Helvetica Neue,Helvetica,Arial,sans-serif;font-weight:normal;font-size:16px;line-height:32px;color:#9fb1c1">
                                    <br />You are receving this email because you have subscribed to low balance alert. You may update your email preferences <a href="'.$admin_url.'admin.php?page=wc-settings&tab=websms_lk#customertemplates" style="color:#03a9f4;font-family:Roboto,Helvetica Neue,Helvetica,Arial,sans-serif;font-weight:normal;text-decoration:none" target="_blank">here</a>.</td>
                            </tr>
                            <tr>
                                <td bgcolor="#fafafa" align="center" style="padding:25px 20px;font-family:Roboto,Helvetica Neue,Helvetica,Arial,sans-serif;font-weight:normal;font-size:12px;line-height:16px;color:#9fb1c1"><img alt="Newsletters.lk SMS" src="https://newsletters.lk/assets/images/logo.png" style="padding-bottom:20px;" width="120">
                                    <br><a style="color:#9fb1c1;font-family:Roboto,Helvetica Neue,Helvetica,Arial,sans-serif;font-weight:normal;text-decoration:none"> &copy; '.date("Y").' SmarteWorks PVT, No 305 , Galle Road, Balapitiya, Sri Lanka.80550</a></td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
</div>';
?>