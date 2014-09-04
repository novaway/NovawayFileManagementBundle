goog.require('goog.testing.jsunit');

function testGetPath() {
    var manager = new novaway.FileManager('/app_dev.php', {
        original: {},
        thumb: {}
    });

    var media = {
        id: 1,
        photo: '/media/name_{-imgformat-}.jpg',
        resume: '/file.txt'
    }

    assertEquals('/app_dev.php/file.txt', manager.getPath(media, 'resume'));
    assertEquals('/app_dev.php/media/name_original.jpg', manager.getPath(media, 'photo', 'original'));
    assertEquals('/app_dev.php/media/name_thumb.jpg', manager.getPath(media, 'photo', 'thumb'));
    assertUndefined(manager.getPath(media, 'photo', 'unknow'));
}

function testTransformPathWithFormat() {
    var manager = new novaway.FileManager('/app_dev.php', {
        original: {},
        thumb: {}
    });

    assertEquals('http://cdn.com/file_original.png', manager.transformPathWithFormat('http://cdn.com/file_{-imgformat-}.png', 'original'));
    assertEquals('http://cdn.com/file_thumb.png', manager.transformPathWithFormat('http://cdn.com/file_{-imgformat-}.png', 'thumb'));
    assertUndefined(manager.transformPathWithFormat('http://cdn.com/file_{-imgformat-}.png', 'unknow'));
}

function testGetFilePath() {
    var manager = new novaway.FileManager('/app_dev.php', {});
    var media = {
        photo: '/test.png'
    };

    assertEquals('/app_dev.php/test.png', manager.getFilePath(media, 'photo'));
}
