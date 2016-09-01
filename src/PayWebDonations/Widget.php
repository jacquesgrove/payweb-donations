<?php
/**
 * The Class for the Widget.
 *
 * @package  PayWeb Donations
 * @author   Jacques Grove
 */
class PayWebDonations_Widget extends WP_Widget
{
    /**
     * Register the Widget.
     */
    public function __construct()
    {
        $widget_ops = array(
            'classname' => 'widget_payweb_donations',
            'description' => __(
                'PayWeb Donation Button',
                PayWebDonations::TEXT_DOMAIN
            )
        );
        parent::__construct('payweb_donations', 'PayWeb Donations Button', $widget_ops);
    }

    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget($args, $instance)
    {
        extract($args);
        // global $payweb_donations;
        $payweb_donations = PayWebDonations::getInstance();

        // Get the settings
        $title = apply_filters('widget_title', $instance['title']);

        echo $before_widget;
        if ($title) {
            echo $before_title . $title . $after_title;
        }
        echo $payweb_donations->generateHtml();
        echo $after_widget;
    }
    
    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update($new_instance, $old_instance)
    {
        $instance = $old_instance;

        $instance['button_text'] = strip_tags(stripslashes($new_instance['button_text']));
        return $instance;
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */
    public function form($instance)
    {
        // Default Widget Settings
        $defaults = array(
            'button_text'     => __('Donate', PayWebDonations::TEXT_DOMAIN)
        );
        $instance = wp_parse_args((array) $instance, $defaults);

        $data = array(
            'instance' => $instance,
            'button_text_id' => $this->get_field_id('button_text'),
            'button_text' => $this->get_field_name('button_text'),
        );
        echo PayWebDonations_View::render('widget-form', $data);
    }
}
