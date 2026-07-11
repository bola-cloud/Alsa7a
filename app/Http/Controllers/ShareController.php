<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DownloadLinkClick;
use App\Models\Post;
use App\Models\CommunityPost;
use App\Models\User;
use App\Models\Club;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class ShareController extends Controller
{
    /**
     * Handle the /share/download route and /share (fallback) route
     */
    public function handle(Request $request, $any = null)
    {
        $ip = $request->ip();
        $userAgent = $request->userAgent();
        
        // Determine OS
        $osType = 'Desktop';
        if (preg_match('/android/i', $userAgent)) {
            $osType = 'Android';
        } elseif (preg_match('/iphone|ipad|ipod/i', $userAgent)) {
            $osType = 'iOS';
        }

        // Determine link type
        $linkType = ($any === 'download' || $any === null || $any === '') ? 'download' : 'general';

        // Async dispatch or simple sync tracking
        // Since it's a redirect, doing an external API call synchronously might delay the redirect.
        // But for simplicity, we will log it. To get country quickly without blocking, we can dispatch a Job.
        // However, we don't have a specific job set up. I will save the IP and we can resolve country later,
        // or just use a very fast API with a low timeout.
        
        $country = null;
        try {
            $response = Http::timeout(2)->get("http://ip-api.com/json/{$ip}?fields=country");
            if ($response->successful() && $response->json('country')) {
                $country = $response->json('country');
            }
        } catch (\Exception $e) {
            // Ignore error to not block redirect
        }

        DownloadLinkClick::create([
            'link_type' => $linkType,
            'ip_address' => $ip,
            'country' => $country ?: 'Unknown',
            'os_type' => $osType,
            'user_agent' => $userAgent,
        ]);

        if ($linkType === 'download') {
            // Get store links from settings if available, else hardcode
            $androidLink = \App\Models\Setting::where('key', 'android_app_link')->value('value') ?: 'https://play.google.com/store/apps/details?id=com.alsa7a.app';
            $iosLink = \App\Models\Setting::where('key', 'ios_app_link')->value('value') ?: 'https://apps.apple.com/app/id123456789';

            if ($osType === 'Android') {
                return redirect()->away($androidLink);
            } elseif ($osType === 'iOS') {
                return redirect()->away($iosLink);
            } else {
                // Desktop or unknown -> show a landing page or redirect to main site
                return redirect('/');
            }
        }

        // General Deep Link Logic (copied from original web.php fallback)
        $title = 'الساحة | AlSaha';
        $description = 'تطبيق الساحة — المنصة الرياضية الأولى';
        $image = asset('app-assets/images/logo.jpeg');

        if ($any) {
            $parts = explode('/', trim($any, '/'));
            if (count($parts) >= 2) {
                $type = $parts[0];
                $idOrSlug = $parts[1];

                try {
                    if ($type === 'post') {
                        $post = Post::find($idOrSlug);
                        if ($post) {
                            $title = $post->content ? Str::limit(strip_tags($post->content), 60) : 'بوست جديد على الساحة';
                            if ($post->image) {
                                $image = $post->image;
                            } elseif ($post->images()->exists()) {
                                $image = $post->images()->first()->url;
                            }
                        }
                    } elseif ($type === 'community' || $type === 'community-post') {
                        $post = CommunityPost::find($idOrSlug);
                        if ($post) {
                            $title = $post->content ? Str::limit(strip_tags($post->content), 60) : 'بوست مجتمع جديد على الساحة';
                            if ($post->image) {
                                $image = $post->image;
                            } elseif ($post->images()->exists()) {
                                $image = $post->images()->first()->url;
                            }
                        }
                    } elseif ($type === 'profile' || $type === 'user') {
                        $user = User::find($idOrSlug);
                        if ($user) {
                            $title = $user->name;
                            $description = 'الملف الشخصي لـ ' . $user->name . ' على تطبيق الساحة';
                            if ($user->profile_photo_path) {
                                $image = asset('storage/' . $user->profile_photo_path);
                            }
                        }
                    } elseif ($type === 'club') {
                        $club = is_numeric($idOrSlug) ? Club::find($idOrSlug) : Club::where('slug', $idOrSlug)->first();
                        if ($club) {
                            $title = $club->name;
                            $description = $club->description ? Str::limit(strip_tags($club->description), 100) : 'نادي رياضي على الساحة';
                            if ($club->logo_url) {
                                $image = preg_match('#^https?://#i', $club->logo_url) ? $club->logo_url : asset('storage/' . $club->logo_url);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Fail silently, use defaults
                }
            }
        }

        return view('app_fallback', compact('title', 'description', 'image'));
    }
}
