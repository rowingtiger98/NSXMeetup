<!-- Login Panel (opened via #loginPanel; requires login.php to have run) -->
<div id="loginPanel" class="modalDialog<?php echo $loginerrorclass; ?>">
    <div>  <a href="#close" title="Close" class="modalclose" onclick="this.closest('.modalDialog').classList.remove('errorevent')">X</a>
        <form action="#" method="post">
            <label for="name">Email :</label>
            <input id="name" name="username" placeholder="email" type="email" autocomplete="username" /><br>
            <label for="password">Password :</label>
            <input id="password" name="password" placeholder="**********" type="password" autocomplete="current-password" />
            <br>
            <input name="submit" class="primary small fit" type="submit" value=" Login " /><br><br>
            <!-- <a href="create_account.php">Click Here to Register</a>, -->
            <a href="account_reset.php"> Forgot your password?</a>
            <?php if ($error !== '') {
                print '<span class="login_error_message">' . htmlspecialchars($error) . '</span>';
            } ?>
        </form>
    </div>
</div>
