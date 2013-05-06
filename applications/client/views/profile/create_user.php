<div class="inner_content">
    <h3>Please keep the information below current.</h3>
    <div class="clear"></div>
    <div class="">
        <?php echo validation_errors(); ?>
        <form action="<?php echo base_url().'profile/create_user'; ?>" method="post" name="profile-form" id="profile-form">

            <div class="p">
                <div class="label">First Name</div>
                <div class="inputs">
                    <input type="text" id="firstname" name="firstname" value="" placeholder="First Name" />
                </div>
                <div class="clear"></div>
            </div>

            <div class="p">
                <div class="label">Last Name</div>
                <div class="inputs">
                    <input type="text" id="lastname" name="lastname" value="" placeholder="Last Name" />
                </div>
                <div class="clear"></div>
            </div>

            <div class="p">
                <div class="label">Email</div>
                <div class="inputs">
                    <input type="text" id="email" name="email" value="" placeholder="Email" />
                </div>
                <div class="clear"></div>
            </div>

            <div class="p">
                <div class="label">Username</div>
                <div class="inputs">
                    <input type="text" id="username" name="username" value="" placeholder="Username" />
                </div>
                <div class="clear"></div>
            </div>

            <div class="p">
                <div class="label">Password</div>
                <div class="inputs">
                    <input type="text" id="password" name="password" value="" placeholder="Password" />
                </div>
                <div class="clear"></div>
            </div>

            <div class="panel_buttons">
                <button name="submit" type="submit" class="button submit">Submit</button>
            </div>

        </form>
    </div>

    <div class="card_container">
        <?php if (isset($bdv) && ( ! is_null($bdv))):?>
        <h6>Your <?php echo $this->config->item('title_of_the_site') ?> Rep:</h6>
        <div class="card">
            <div class="logo small"></div>
            <div class="clear"></div>
            <div class="name"><?php echo $bdv['firstname']?>&nbsp;<?php echo $bdv['lastname']?></div>
            <div class="phone"><?php echo $bdv['phone']?></div>
            <div class="email"><?php echo $bdv['email']?></div>
            <div class="address"><?php echo $bdv['address']?></div>
        </div>

        <button id="save_vcard" type="submit" class="button download">Save Contact to Outlook</button>
        <?php endif?>
        <div class="clear"></div>
    </div>

    <div class="clear"></div>
</div>