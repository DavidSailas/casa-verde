<?php

/*
|--------------------------------------------------------------------------
| Yoga Reset page defaults
|--------------------------------------------------------------------------
|
| Placeholder content and images for the Yoga Reset page. Anything edited in
| the admin (home_contents rows whose section starts with "yoga_") overrides
| these values. The migration loads them into the admin so they can be edited
| there.
|
*/

return [

    'hero' => [
        'eyebrow' => 'Yoga Reset at Casa Verde',
        'title' => 'Breathe. Stretch. Reset.',
        'text' => 'Guided yoga on the cliff edge, with the sea below and the morning sky above.',
        'image' => 'images/experiences/hero.jpg',
        'button_text' => 'Book your reset',
        'button_url' => '/#contact',
    ],

    // The outlined button next to the main hero button (admin: "Top banner: second button").
    'hero_link' => [
        'text' => 'What is included',
        'url' => '#package',
    ],

    // Small pills above the headline, e.g. dates or places left (admin: "Top banner: pills").
    'chips' => [
        ['text' => 'Sunrise and sunset classes', 'style' => 'light'],
        ['text' => 'All levels welcome', 'style' => 'dark'],
    ],

    // Short highlights under the buttons (admin: "Top banner: highlights").
    'facts' => [
        '2 sessions a day',
        '60 minutes per class',
        'Mats and props provided',
        'Cliffside deck with sea view',
    ],

    // The light card right under the top banner (admin: "Notice card").
    'notice' => [
        'label' => 'Good to know',
        'title' => 'Reserve directly with our team.',
        'text' => "Hotel guests and visitors are both welcome. Tell us which classes you would like and when you are staying, and we will confirm availability with you personally.\n\nAsk our team for the current class times and prices.",
    ],

    // Text beside the day-by-day timeline (admin: "Programme: day by day text").
    'flow' => [
        'title' => 'The flow, hour by hour',
        'note' => 'Sample day',
        'text' => 'A day at Casa Verde moves gently from movement to rest. Times can shift with the weather and resort operations; we confirm them when you book.',
    ],

    // Day tabs for the timeline. Empty by default: one timeline for every day (admin: "Programme: days").
    'days' => [],

    'care' => [
        'title' => 'Flexibility is part of the care',
        'items' => [
            'Timings may adjust to weather, tide and resort operations.',
            'Mats and props are provided for every class.',
            'Every class is suitable for beginners and can be adapted to your needs.',
        ],
        'note' => 'Times are confirmed with your booking.',
    ],

    // Open classes: one entry per session, grouped by the day label (admin: "Open classes: sessions").
    'classes' => [
        'note' => 'Ask our team for class prices.',
        'small' => 'Reserving sends us your request; our team confirms your place.',
        'sessions' => [
            ['group' => 'Every day', 'name' => '6:00 AM · Sunrise Flow', 'meta' => 'All levels', 'button_text' => 'Reserve', 'button_url' => '/#contact'],
            ['group' => 'Every day', 'name' => '5:00 PM · Sunset Restore', 'meta' => 'Gentle', 'button_text' => 'Reserve', 'button_url' => '/#contact'],
            ['group' => 'By appointment', 'name' => 'Any time · Private session', 'meta' => 'Any level', 'button_text' => 'Request', 'button_url' => '/#contact'],
        ],
    ],

    'resort' => [
        'title' => 'A small resort on the cliff',
        'text' => "Casa Verde Cliff Resort & Spa sits on the cliff above the sea on Camotes Island, a quiet place to slow down.\n\nBetween classes there is time for the pool, the spa and long meals with a view of the water.",
        'photos' => [
            ['image' => 'images/wellness4.jpg', 'caption' => 'Yoga above the sea'],
            ['image' => 'images/exp1.jpg', 'caption' => 'Sunset on the island'],
            ['image' => 'images/exp3.jpg', 'caption' => 'Camotes Island from above'],
        ],
        'spotlight' => [
            'image' => 'images/experiences/hero.jpg',
            'badge' => 'The setting',
            'title' => 'The cliffside deck',
            'text' => 'Our open-air deck sits right on the cliff edge, with the sea below and the sky above: a quiet place to practise and to breathe.',
        ],
    ],

    'gallery' => [
        'title' => 'Impressions of Casa Verde',
        'text' => 'Yoga above the sea, quiet corners and island light. Swipe through.',
        'items' => [
            ['image' => 'images/wellness4.jpg', 'caption' => 'Yoga above the sea'],
            ['image' => 'images/exp1.jpg', 'caption' => 'Sunset on the island'],
            ['image' => 'images/wellness1.jpg', 'caption' => 'Spa treatments after class'],
            ['image' => 'images/wellness2.jpg', 'caption' => 'Time to unwind'],
            ['image' => 'images/wellness3.jpg', 'caption' => 'A flower bath at sunset'],
            ['image' => 'images/exp3.jpg', 'caption' => 'Camotes Island from above'],
        ],
    ],

    'teacher' => [
        'title' => 'Our yoga teachers',
        'role' => 'Mindful guidance for every level',
        'text' => "Our teachers guide every class with care, offering easier or deeper options so you can practise at your own pace.\n\nWhether you are new to yoga or have a daily practice, you are welcome here.",
        'image' => 'images/wellness4.jpg',
        'caption' => 'Guided by experienced teachers',
        'styles' => ['Vinyasa flow', 'Restorative', 'Breathwork', 'Meditation'],
    ],

    'package' => [
        'title' => 'What a Yoga Reset includes',
        'columns' => [
            ['title' => 'Included', 'style' => 'included', 'items' => [
                'Yoga classes with an experienced teacher',
                'Mats and props for every class',
                'The cliffside deck with its sea view',
            ]],
            ['title' => 'Available on request', 'style' => 'extras', 'items' => [
                'Private or small-group sessions',
                'Spa treatments after class',
                'Help planning your stay with us',
            ]],
        ],
        'price' => [
            'title' => 'Prices',
            'big' => 'On request',
            'text' => 'Contact our team for current class and stay prices.',
            'bullets' => [
                'Hotel guests and visitors welcome',
                'Private sessions by appointment',
            ],
            'button_text' => 'Request a place',
            'button_url' => '/#contact',
        ],
    ],

    /*
    | The blocks of the page after the top banner, in their starting order. In the admin
    | (Yoga Reset > Page layout) the name, the position and the visible switch of each block can
    | be changed. The name is also the small label above the block's heading.
    */
    'layout' => [
        'notice' => ['title' => 'Notice card'],
        'about' => ['title' => 'The practice'],
        'programme' => ['title' => 'Programme'],
        'classes' => ['title' => 'Open classes'],
        'resort' => ['title' => 'The resort'],
        'gallery' => ['title' => 'Gallery'],
        'teacher' => ['title' => 'Your teacher'],
        'package' => ['title' => 'The package'],
        'faq' => ['title' => 'Questions'],
        'quote' => ['title' => 'Guest words'],
        'cta' => ['title' => 'Booking banner'],
    ],

    'stats' => [
        ['title' => '2', 'eyebrow' => 'Daily sessions', 'icon' => 'sunset'],
        ['title' => '60 min', 'eyebrow' => 'Per class', 'icon' => 'clock'],
        ['title' => 'All levels', 'eyebrow' => 'Welcome', 'icon' => 'lotus'],
        ['title' => 'Sea view', 'eyebrow' => 'Cliffside deck', 'icon' => 'cliff'],
    ],

    'intro' => [
        'eyebrow' => 'Slow down',
        'title' => 'A gentle reset for body and mind.',
        'text' => 'Join our sessions to release tension, restore energy and find calm, whether you are brand new to yoga or already have a daily practice.',
    ],

    'types' => [
        [
            'title' => 'Sunrise Flow',
            'eyebrow' => '6:00 AM · 60 min · All levels',
            'text' => 'Wake up softly with an energising flow as the sun rises over the water.',
            'image' => 'images/wellness4.jpg',
            'icon' => 'lotus',
            'button_text' => 'Discover more',
            'button_url' => '/#contact',
        ],
        [
            'title' => 'Sunset Restore',
            'eyebrow' => '5:00 PM · 60 min · Gentle',
            'text' => 'Slow, restorative poses and breathwork to close the day in stillness.',
            'image' => 'images/exp1.jpg',
            'icon' => 'lotus',
            'button_text' => 'Discover more',
            'button_url' => '/#contact',
        ],
        [
            'title' => 'Private Sessions',
            'eyebrow' => 'By appointment · 60 min · Any level',
            'text' => 'One-to-one or small-group classes shaped around your pace and goals.',
            'image' => 'images/dining3.jpg',
            'icon' => 'lotus',
            'button_text' => 'Discover more',
            'button_url' => '/#contact',
        ],
    ],

    'schedule' => [
        'eyebrow' => 'Programme',
        'title' => 'How a Yoga Reset day flows.',
        'items' => [
            ['title' => '6:00 AM', 'eyebrow' => 'Sunrise Flow', 'text' => 'Gentle movement and breathwork as the sky turns gold.'],
            ['title' => '8:00 AM', 'eyebrow' => 'Island Breakfast', 'text' => 'Fresh fruit and a slow breakfast with a view of the sea.'],
            ['title' => '11:00 AM', 'eyebrow' => 'Meditation & Stretch', 'text' => 'A short guided session to keep the calm going through the day.'],
            ['title' => '5:00 PM', 'eyebrow' => 'Sunset Restore', 'text' => 'Slow, supported poses to let go of the day as the light fades.'],
        ],
    ],

    'features' => [
        'title' => 'Everything you need to reset.',
        'items' => [
            ['title' => 'Cliffside Deck', 'text' => 'An open-air practice space with ocean views.', 'icon' => 'cliff'],
            ['title' => 'Expert Teachers', 'text' => 'Mindful guidance for every level.', 'icon' => 'lotus'],
            ['title' => 'Mats & Props', 'text' => 'Everything provided, just bring yourself.', 'icon' => 'clipboard'],
            ['title' => 'Island Calm', 'text' => 'Quiet surroundings made for deep rest.', 'icon' => 'island'],
        ],
    ],

    'personal' => [
        'eyebrow' => 'Your pace',
        'title' => 'Practice that fits your stay.',
        'text' => 'Drop in for a single class or build a full reset into your visit. Our team will help you choose the sessions that suit you best.',
        'image' => 'images/dining3.jpg',
        'button_text' => 'Start your reset',
        'button_url' => '/#contact',
    ],

    'quote' => [
        'text' => 'Waking up to yoga above the sea was the best part of our stay. I left rested in a way I had not felt in years.',
        'title' => 'Guest name',
        'eyebrow' => 'Guest from Germany',
    ],

    'faq' => [
        'eyebrow' => 'Good to know',
        'title' => 'Questions, answered.',
        'items' => [
            ['title' => 'Do I need yoga experience?', 'text' => 'Not at all. Sunrise Flow and Sunset Restore welcome every level, and your teacher will offer easier or deeper options for each pose.'],
            ['title' => 'What should I bring?', 'text' => 'Just yourself and comfortable clothes. Mats and props are provided, and we recommend a water bottle and a light layer for the early morning breeze.'],
            ['title' => 'Do I have to be a hotel guest to join?', 'text' => 'Please contact our team to check availability for visitors. Guests staying at Casa Verde can simply reserve a place at reception or through the contact form.'],
            ['title' => 'Can I book a private session?', 'text' => 'Yes. Private and small-group sessions are available by appointment, at a time and pace that suits you. Send us a message and we will arrange it.'],
            ['title' => 'What happens if it rains?', 'text' => 'We move the class to a covered, sheltered space so your practice carries on, with the same teacher and the same sea air.'],
        ],
    ],

    'cta' => [
        'title' => 'Ready for your Yoga Reset?',
        'image' => 'images/experiences/hero.jpg',
        'buttons' => [
            ['text' => 'Book your stay', 'url' => '/book'],
            ['text' => 'Contact our team', 'url' => '/#contact'],
        ],
    ],

];
