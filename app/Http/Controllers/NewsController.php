<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

// class NewsController extends Controller
// {
//    public function show(){
//         $new['title'] = 'Bản tin thời tiết';
//         $new['content'] = 'Trời đang mưa phùn, nhiệt độ <20*C';
//         return view('news.detail', ['new'=> $new]);
//    }
// }
class NewsController extends Controller
{
   public function show($id){
      $new[1]['title'] = 'Bản tin thời tiết';
      $new[1]['content'] = 'Trời đang mưa phùn, nhiệt độ <20°C';
      $new[2]['title'] = 'Bản tin thời sự';
      $new[2]['content'] = 'Chủ tịch nước @abc có chuyến thăm ngoại giao...';
      // if (!isset($new[$id])) {
      //    return abort(404, 'Bản tin không tồn tại'); // Hiển thị lỗi 404 nếu ID không hợp lệ
      // }
      return view('news.detail', ['new'=> $new[$id]]);
   }
}