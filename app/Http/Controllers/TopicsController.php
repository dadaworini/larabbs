<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\TopicRequest;
use App\Models\Category;
use Auth;
use App\Handlers\ImageUploadHandler;

class TopicsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['index', 'show']]);
    }

	public function index(Request $request, Topic $topic)
	{

		$topics = $topic->withOrder($request->order)->paginate(20);
		return view('topics.index', compact('topics'));
	}

    public function show(Topic $topic)
    {
        return view('topics.show', compact('topic'));
    }

	public function create(Topic $topic, Category $category)
	{
        $categories = $category->all();
		return view('topics.create_and_edit', compact('topic', 'categories'));
	}

	public function store(TopicRequest $request, Topic $topic)
	{

		$topic->fill($request->all());
        $topic->user_id = Auth::id();
        $topic->save();
		return redirect()->route('topics.show', $topic->id)->with('message', 'Created successfully.');
	}

	public function edit(Topic $topic)
	{
        $this->authorize('update', $topic);
		return view('topics.create_and_edit', compact('topic'));
	}

	public function update(TopicRequest $request, Topic $topic)
	{
		$this->authorize('update', $topic);
		$topic->update($request->all());

		return redirect()->route('topics.show', $topic->id)->with('message', 'Updated successfully.');
	}

	public function destroy(Topic $topic)
	{
		$this->authorize('destroy', $topic);
		$topic->delete();

		return redirect()->route('topics.index')->with('message', 'Deleted successfully.');
	}

    public function uploadImage(Request $request, ImageUploadHandler $uploader)
    {
        //初始化返回数据，默认是失败的
        $data = [
            'success' => false,
            'msg'     => '上传失败！',
            'file_path' => ''
        ];

        //判断是否有文件上传
        if ($file = $request->upload_file) {
            //保持图片到本地

            $result = $uploader->save($request->upload_file, 'topics', \Auth::id(), 1024);

            //图片保持成功的 话

            if ($result) {
                $data['file_path'] = $result['path'];
                $data['success']   = true;
                $data['msg']       = "上传成功！";

            }
        }

        return $data;
    }
}