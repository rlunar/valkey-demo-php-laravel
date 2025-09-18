<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class BlogController extends Controller
{
    /**
     * Display the blog landing page.
     */
    public function index(): Response
    {
        $blogData = $this->getMockBlogData();

        return Inertia::render('blog/index', [
            'blog' => $blogData,
        ]);
    }

    /**
     * Get mock data for the blog page.
     */
    private function getMockBlogData(): array
    {
        return [
            'siteName' => 'Large',
            'categories' => [
                'World',
                'U.S.',
                'Technology',
                'Design',
                'Culture',
                'Business',
                'Politics',
                'Opinion',
                'Science',
                'Health',
                'Style',
                'Travel'
            ],
            'featuredPost' => [
                'title' => 'Featured post',
                'excerpt' => 'This is a wider card with supporting text below as a natural lead-in to additional content. This content is a little bit longer.',
                'readMoreUrl' => '#'
            ],
            'secondaryPosts' => [
                [
                    'id' => '1',
                    'title' => 'Post title',
                    'category' => 'World',
                    'date' => 'Nov 12',
                    'excerpt' => 'This is a wider card with supporting text below as a natural lead-in to additional content.',
                    'readMoreUrl' => '#',
                    'thumbnailUrl' => null
                ],
                [
                    'id' => '2',
                    'title' => 'Post title',
                    'category' => 'Design',
                    'date' => 'Nov 11',
                    'excerpt' => 'This is a wider card with supporting text below as a natural lead-in to additional content.',
                    'readMoreUrl' => '#',
                    'thumbnailUrl' => null
                ]
            ],
            'mainPosts' => [
                [
                    'id' => '3',
                    'title' => 'Sample blog post',
                    'author' => 'Mark',
                    'date' => 'January 1, 2021',
                    'content' => '<p>This blog post shows a few different types of content that\'s supported and styled with Bootstrap. Basic typography, images, and code are all supported.</p><hr><p>Cum sociis natoque penatibus et magnis <a href="#">dis parturient montes</a>, nascetur ridiculus mus. Aenean eu leo quam. Pellentesque ornare sem lacinia quam venenatis vestibulum. Sed posuere consectetur est at lobortis. Cras mattis consectetur purus sit amet fermentum.</p><blockquote><p>Curabitur blandit tempus porttitor. <strong>Nullam quis risus eget urna mollis</strong> ornare vel eu leo. Nullam id dolor id nibh ultricies vehicula ut id elit.</p></blockquote><p>Etiam porta <em>sem malesuada magna</em> mollis euismod. Cras mattis consectetur purus sit amet fermentum. Aenean lacinia bibendum nulla sed consectetur.</p><h2>Heading</h2><p>Vivamus sagittis lacus vel augue rutrum faucibus dolor auctor. Duis mollis, est non commodo luctus, nisi erat porttitor ligula, eget lacinia odio sem nec elit. Morbi leo risus, porta ac consectetur ac, vestibulum at eros.</p><h3>Sub-heading</h3><p>Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.</p><pre><code>Example code block</code></pre><p>Aenean lacinia bibendum nulla sed consectetur. Etiam porta sem malesuada magna mollis euismod. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa.</p><h3>Sub-heading</h3><p>Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Aenean lacinia bibendum nulla sed consectetur. Etiam porta sem malesuada magna mollis euismod. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus.</p><ul><li>Praesent commodo cursus magna, vel scelerisque nisl consectetur et.</li><li>Donec id elit non mi porta gravida at eget metus.</li><li>Nulla vitae elit libero, a pharetra augue.</li></ul><p>Donec ullamcorper nulla non metus auctor fringilla. Nulla vitae elit libero, a pharetra augue.</p><ol><li>Vestibulum id ligula porta felis euismod semper.</li><li>Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.</li><li>Maecenas sed diam eget risus varius blandit sit amet non magna.</li></ol><p>Cras mattis consectetur purus sit amet fermentum. Sed posuere consectetur est at lobortis.</p>'
                ],
                [
                    'id' => '4',
                    'title' => 'Another blog post',
                    'author' => 'Jacob',
                    'date' => 'December 23, 2020',
                    'content' => '<p>Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Aenean eu leo quam. Pellentesque ornare sem lacinia quam venenatis vestibulum. Sed posuere consectetur est at lobortis. Cras mattis consectetur purus sit amet fermentum.</p><blockquote><p>Curabitur blandit tempus porttitor. <strong>Nullam quis risus eget urna mollis</strong> ornare vel eu leo. Nullam id dolor id nibh ultricies vehicula ut id elit.</p></blockquote><p>Etiam porta <em>sem malesuada magna</em> mollis euismod. Cras mattis consectetur purus sit amet fermentum. Aenean lacinia bibendum nulla sed consectetur.</p><p>Vivamus sagittis lacus vel augue rutrum faucibus dolor auctor. Duis mollis, est non commodo luctus, nisi erat porttitor ligula, eget lacinia odio sem nec elit. Morbi leo risus, porta ac consectetur ac, vestibulum at eros.</p>'
                ],
                [
                    'id' => '5',
                    'title' => 'New feature',
                    'author' => 'Chris',
                    'date' => 'December 14, 2020',
                    'content' => '<p>Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Aenean lacinia bibendum nulla sed consectetur. Etiam porta sem malesuada magna mollis euismod. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus.</p><ul><li>Praesent commodo cursus magna, vel scelerisque nisl consectetur et.</li><li>Donec id elit non mi porta gravida at eget metus.</li><li>Nulla vitae elit libero, a pharetra augue.</li></ul><p>Etiam porta <em>sem malesuada magna</em> mollis euismod. Cras mattis consectetur purus sit amet fermentum. Aenean lacinia bibendum nulla sed consectetur.</p>'
                ]
            ],
            'sidebar' => [
                'aboutText' => 'Etiam porta sem malesuada magna mollis euismod. Cras mattis consectetur purus sit amet fermentum. Aenean lacinia bibendum nulla sed consectetur.',
                'recentPosts' => [
                    [
                        'title' => 'Example blog post',
                        'date' => 'January 1, 2021',
                        'url' => '#',
                        'thumbnailUrl' => null
                    ],
                    [
                        'title' => 'Another blog post',
                        'date' => 'December 23, 2020',
                        'url' => '#',
                        'thumbnailUrl' => null
                    ],
                    [
                        'title' => 'New feature',
                        'date' => 'December 14, 2020',
                        'url' => '#',
                        'thumbnailUrl' => null
                    ]
                ],
                'archives' => [
                    ['label' => 'March 2021', 'url' => '#'],
                    ['label' => 'February 2021', 'url' => '#'],
                    ['label' => 'January 2021', 'url' => '#'],
                    ['label' => 'December 2020', 'url' => '#'],
                    ['label' => 'November 2020', 'url' => '#'],
                    ['label' => 'October 2020', 'url' => '#'],
                    ['label' => 'September 2020', 'url' => '#'],
                    ['label' => 'August 2020', 'url' => '#'],
                    ['label' => 'July 2020', 'url' => '#'],
                    ['label' => 'June 2020', 'url' => '#'],
                    ['label' => 'May 2020', 'url' => '#'],
                    ['label' => 'April 2020', 'url' => '#']
                ],
                'externalLinks' => [
                    ['label' => 'GitHub', 'url' => 'https://github.com'],
                    ['label' => 'Twitter', 'url' => 'https://twitter.com'],
                    ['label' => 'Facebook', 'url' => 'https://facebook.com']
                ]
            ],
            'pagination' => [
                'hasOlder' => true,
                'hasNewer' => false,
                'olderUrl' => '/blog?page=2',
                'newerUrl' => null
            ]
        ];
    }
}
