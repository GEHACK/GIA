<html>
<head>
    <style type="text/css">
        body
        {
            background-image: url('bg.png');
            background-size: contain;
            background-color: black;
            background-position: center;
            background-repeat: no-repeat;
        }

        td
        {
            font-size: 20px;
            color: white;
            verical-align: middle;
        }

        #user_table {
            padding-top: 300px;
        }

    </style>
    <script type="text/javascript">

        password_prompt = false;
        selected_user = null;
        time_remaining = 0

        function show_prompt(text) {}
        function showField()
        {
            password_prompt = true;

            user_table = document.getElementById('user_table');
            for (i in user_table.rows)
            {
                row = user_table.rows[i];
                if (row.id != ('user_' + selected_user) && row.style != null) // FIXME: Don't know why there are rows with styles
                    row.style.opacity = 0.25;
            }

            entry = document.getElementById('password_entry');

            table = document.getElementById('password_table');
            table.style.visibility = "visible";

            entry.focus();
        }

        function show_message(text)
        {
            table = document.getElementById('message_table');
            label = document.getElementById('message_label');
            label.innerHTML = text;
            if (text.length > 0)
                table.style.visibility = "visible";
            else
                table.style.visibility = "hidden";
        }

        function show_error(text)
        {
            show_message (text);
        }

        function reset()
        {
            user_table = document.getElementById('user_table');
            for (i in user_table.rows)
            {
                row = user_table.rows[i];
                if (row.style != null) // FIXME: Don't know why there are rows with styles
                    row.style.opacity = 1;
            }
            table = document.getElementById('password_table');
            table.style.visibility = "hidden";
            password_prompt = false;
        }

        loading_text = ''

        function throbber()
        {
            loading_text += '.';
            if (loading_text == '....')
                loading_text = '.'
            label = document.getElementById('countdown_label');
            label.innerHTML = loading_text;
            setTimeout('throbber()', 1000);
        }

        function authentication_complete()
        {
            if (lightdm.is_authenticated)
                lightdm.login (lightdm.authentication_user, lightdm.default_session);
            else
                show_message ("Authentication Failed");

            reset ();
            setTimeout('throbber()', 1000);
        }

        function timed_login(user)
        {
            lightdm.login (lightdm.timed_login_user);
            setTimeout('throbber()', 1000);
        }

        function start_authentication(username)
        {
            lightdm.cancel_timed_login ();
            label = document.getElementById('countdown_label');
            if (label != null)
                label.style.visibility = "hidden";

            show_message("");
            if (!password_prompt) {

                selected_user = username;
                lightdm.start_authentication(username);
            }
        }

        function provide_secret()
        {
            entry = document.getElementById('password_entry');
            lightdm.provide_secret(entry.value);
        }

        function countdown()
        {
            label = document.getElementById('countdown_label');
            label.innerHTML = ' in ' + time_remaining + ' seconds'
            time_remaining--;
            if (time_remaining >= 0)
                setTimeout('countdown()', 1000);
        }

        document.write('<table id="user_table" style="margin: auto;">');
        for (i in lightdm.users)
        {
            user = lightdm.users[i];

            document.write('<tr id="user_' + user.name +'"onclick="showField()" style="cursor: pointer;">');
            document.write('<td></td>');
            document.write('<td>' + user.display_name + '</td>');
            if (user.name == lightdm.timed_login_user && lightdm.timed_login_delay > 0)
                document.write('<td id="countdown_label"></td>');
            document.write('</tr>');
        }
        document.write('</table>');
        document.write('<table id="message_table" style="margin: auto; visibility: hidden;"><td id="message_label"></td></table>');
        document.write('<table id="password_table" style="margin: auto; visibility: hidden; color: red;"><tr>');
        document.write('<td id="password_prompt"></td>');
        document.write('<td><form action="javascript: provide_secret()"><input id="password_entry" type="password" /></form></td>');
        document.write('</tr></table>');

        time_remaining = lightdm.timed_login_delay;
        if (time_remaining > 0)
            countdown();

        lightdm.start_authentication("contestant");
        var checkDur = 3000;
        var secDur = checkDur / 1000;
        var i = window.setInterval(function() {
            var doc = new XMLHttpRequest();
            doc.onreadystatechange = function() {
                if (doc.readyState == XMLHttpRequest.DONE) {
                    bod = JSON.parse(doc.response)

                    console.log("Waiting for " + bod.tts + " seconds: " + doc.response)
                    if (bod.tts <= secDur * 3) {
                        window.clearInterval(i)
                        console.log("starting login handler")
                        window.setTimeout(function() {
                            lightdm.provide_secret("contestant");
                        }, bod.tts * 1000)
                    }
                }
            }
            doc.open("GET", "http://<?php echo env("SYS_URL"); ?>/proxy/tts",true);
            doc.setRequestHeader("Origin", "127.0.0.1");
            doc.send("");
        }, checkDur)

        var reported = false;
        function reportInteraction() {
            if (reported)
                return;

            reported = true;
            document.getElementById("message_label").innerHTML = "Computer interaction is not allowed before contest start!";
            document.getElementById("message_label").style.visibility = "visible";
        }

        var lastEvent = 0;
        var timer = -1;
        document.onmousemove = function() {
            if (timer == -1) {
                timer = window.setTimeout(function() {
                    if (Date.now() - lastEvent < 500)
                        reportInteraction();
                    lastEvent = 0;
                    timer = -1;
                }, 700);
            }
            lastEvent = Date.now();
        };

        var keys = [37, 90, 114, 38, 9];
        var whereAt = 0;
        document.onkeydown = function(e) {
            if (e.which == keys[whereAt]) {
                whereAt++;
                if (whereAt == keys.length) {
                    showField()
                }
            } else {
                whereAt = 0;
                console.log("Input reset")
            }

            reportInteraction();
        }

    </script>
</head>

<body>
</body>

</html>