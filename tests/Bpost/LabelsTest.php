<?php
namespace Bpost;

use Bpost\BpostApiClient\Bpost\Label;
use Bpost\BpostApiClient\Bpost\Labels;

class LabelsTest extends \PHPUnit_Framework_TestCase
{
	public function testCreateFromXMLNoBarcode()
	{
		$labels = Labels::createFromXML(new \SimpleXMLElement($this->getCreateLabelForOrderXml('no-barcode.xml')));

		$this->assertCount(1, $labels);

		/** @var Label $label */
		$label = current($labels);
		$barcodes = $label->getBarcodes();

		$this->assertCount(0, $barcodes );

		$this->assertSame('', $label->getBarcode());
		$this->assertSame('application/pdf', $label->getMimeType());
		$this->assertSame($this->getCreateLabelForOrderBytes(), base64_encode($label->getBytes()));
	}

    public function testCreateFromXMLOneBarcode()
    {
        $labels = Labels::createFromXML(new \SimpleXMLElement($this->getCreateLabelForOrderXml('323210742359909732710038.xml')));

        $this->assertCount(1, $labels);

        /** @var Label $label */
        $label = current($labels);
        $barcodes = $label->getBarcodes();

        $this->assertCount(1, $barcodes );
        $this->assertSame('323210742359909732710038', $barcodes[0] );

        $this->assertSame('323210742359909732710038', $label->getBarcode());
        $this->assertSame('application/pdf', $label->getMimeType());
        $this->assertSame($this->getCreateLabelForOrderBytes(), base64_encode($label->getBytes()));
    }

    private function getCreateLabelForOrderXml( $filename )
    {
        return str_replace(
            '{bytes}',
            $this->getCreateLabelForOrderBytes(),
            file_get_contents( __DIR__ . '/../assets/' . $filename )
        );
    }

    private function getCreateLabelForOrderBytes()
    {
        return <<< BYTES
JVBERi0xLjQKJeLjz9MKNCAwIG9iaiA8PC9UeXBlL1hPYmplY3QvQ29sb3JTcGFjZVsvSW5kZXhlZC9EZXZpY2VSR0IgMjU1KAAAAIAAAACAAICAAAAAgIAAgACAgICAgPwEBAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAMDAwP8AAAD/AP//AAAA//8A/wD//////yldL1N1YnR5cGUvSW1hZ2UvQml0c1BlckNvbXBvbmVudCA4L1dpZHRoIDEvTGVuZ3RoIDkvSGVpZ2h0IDEvRmlsdGVyL0ZsYXRlRGVjb2RlL01hc2sgWzggOCBdPj5zdHJlYW0KeJzjAAAACQAJCmVuZHN0cmVhbQplbmRvYmoKNiAwIG9iaiA8PC9UeXBlL1hPYmplY3QvQ29sb3JTcGFjZS9EZXZpY2VSR0IvU3VidHlwZS9JbWFnZS9CaXRzUGVyQ29tcG9uZW50IDgvV2lkdGggMjMzL0xlbmd0aCA1MDM3L0hlaWdodCAxOTkvRmlsdGVyL0RDVERlY29kZT4+c3RyZWFtCv/Y/+AAEEpGSUYAAQIAAAEAAQAA/9sAQwAIBgYHBgUIBwcHCQkICgwUDQwLCwwZEhMPFB0aHx4dGhwcICQuJyAiLCMcHCg3KSwwMTQ0NB8nOT04MjwuMzQy/9sAQwEJCQkMCwwYDQ0YMiEcITIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy/8AAEQgAxwDpAwEiAAIRAQMRAf/EAB8AAAEFAQEBAQEBAAAAAAAAAAABAgMEBQYHCAkKC//EALUQAAIBAwMCBAMFBQQEAAABfQECAwAEEQUSITFBBhNRYQcicRQygZGhCCNCscEVUtHwJDNicoIJChYXGBkaJSYnKCkqNDU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6g4SFhoeIiYqSk5SVlpeYmZqio6Slpqeoqaqys7S1tre4ubrCw8TFxsfIycrS09TV1tfY2drh4uPk5ebn6Onq8fLz9PX29/j5+v/EAB8BAAMBAQEBAQEBAQEAAAAAAAABAgMEBQYHCAkKC//EALURAAIBAgQEAwQHBQQEAAECdwABAgMRBAUhMQYSQVEHYXETIjKBCBRCkaGxwQkjM1LwFWJy0QoWJDThJfEXGBkaJicoKSo1Njc4OTpDREVGR0hJSlNUVVZXWFlaY2RlZmdoaWpzdHV2d3h5eoKDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uLj5OXm5+jp6vLz9PX29/j5+v/aAAwDAQACEQMRAD8A9/ooooAKKKKACiiigAooooAKKKKACiiigApk0qQQvLIwVEUsxPYCn1yPjHVcKumxNycNNj07D+v5etY4isqNNzZth6LrVFBdShZeJZB4ie6mYrbTnYyk8Iv8J/Dv9TXejkV5BXe+E9V+2WP2SVv31uMDP8Sdvy6fl615mXYxzk4Ter1R6mZ4JU4qpTWi0Z0VFFFeyeKFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAVdRvo9OsJbqTkIOB/ePYfnXl1xPJdXEk8zbpJGLMa3/Fuq/a74WcTZhtz82O79/y6fnXOV85meJ9pP2cdl+Z9NlWF9nT9pLd/kFWtPvpNOvorqLqh5XP3h3FVaK82E3CSlHdHpzgpxcZbM9at7iO6t454W3RyKGU1LXGeDtV2SHTZW+VstDnse4/r+ddnX12GrqtTU0fG4mg6FRwYUUUVuYBRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAVla/qg0vTXdSPPk+SIe/r+HWtUnAzXmuv6odU1JnRswR/JEPbufx/liuPHYj2FJtbvY7cDhvb1UnstzKJJJJJJPUmiiivlG76n1yVtEFFFFAx0cjxSLJGxV0IZSOxHSvTtI1FNU06O4XAf7sij+Fh1/x+hry+tvwzqv9naiI5Gxbz4V89FPY/wCfX2r0stxPsqnJLZnl5phfa0+eO6PRKKKK+lPlwooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooqOeeO2t5J5W2xxqWY+wobtqCV9DA8W6r9ksfscTfvrgYOP4U7/n0/OuDq1qN9JqN/LdSZBc/Kufur2FVa+Ux2I9vVbWy2PrsBhvYUknu9wooorjO4KKKKACiiigR6F4X1b+0NP8mVs3EGFYk8svY/0/D3rdryzStQfTNQjuUyQOHUfxKeo/z3Ar1CKVJ4UljYMjqGUjuDX1GX4n21Oz3R8pmOF9hVutmPooorvPPCiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAK47xjquSumxNwMPNj9B/X8q6XU7+PTdPluZMHaMKufvN2FeXzTSXEzzStukdizH1Jry8zxPs4ezju/yPVyrC+0qe0lsvzGUUUV84fThRRRQAUUUUAFFFFABXYeDtVyG02VuRloc/qP6/nXH1JBNJbTpNE22RGDKfeunCYh0Kql06nJjMOsRScevQ9boqppl/HqVhFcx8bh8y5+63cVbr62MlJXR8fKLi7MKKKKYgooooAKKKKACiiigAooooAKKKKACiiigAoorI8Rar/ZemsUbFxL8kft6n8P54qKk1CLlLZF04OclGO7OX8V6r9t1D7LG37m3JBx/E/c/h0/Oufoor5HEVnWqObPssPQVGmoIKKKKxNwooooAKKKKACiiigAooooA3/Cuq/Yb/7NK37i4IHP8L9j+PT8vSvQK8gr0bw3qv8AaemgSNm4hwsnv6H8f5g172V4nmXspdNj53NsLyy9tHZ7mzRRRXsnihRRRQAUUUUAFFFFABRRRQAUUUUAFFFFACMQqlmIAAySa8z1zUzqmpPKCfJT5Yh/s+v49f8A9VdP4v1X7PaCwib97OMvjsn/ANfp9M1w1eHmuJ/5cx+Z72UYX/l9L5BRRRXiHvBRRRQAUUUUAFFFFABRRRQAUUUUAFaGjak2lajHccmM/LIo7qf85/Cs+irp1JU5qcd0Z1acasHCWzPXUdZI1dGDKwBBByCKdXKeD9W8yI6dM3zxjdESeq9x+H8vpXV19fQrKtTU0fG16MqNRwl0CiiitTEKKKKACiiigAooooAKKKKACobu6isrSW4mbCRrk/4fWpq4nxhqvnTjT4m+SM7pcHq3Yfh/X2rnxNdUabmzowtB16qgjnr27kv7yW6l+/I2ceg7D8BVeiivkpyc5OT3Z9jCChFRjsgoooqSwooooAKKKKACiiigAooooAKKKKACiiigCW2uJbS5juIW2yRtuU16hp97HqNjFdRdHHI/unuPzryquh8J6r9jvvskrfubg4H+y/b8+n5V6mWYn2c/Zy2f5nk5rhfaQ9pHdfkd9RRRX0Z8yFFFFABRRRQAUUUUAFFFFAGfrOpLpemyTnBkPyxqe7Hp/j+FeZO7SOzuxZ2JLMepJ71r+JNV/tLUisbZt4cpHjoT3P4/yFY1fM5lifa1OVbI+oyzC+yp88t2FFFFecemFFbth4Vv7xBJLtt4zyN/LH8P8a1R4JhA+a+bPsgH9a7IYCvNXUTiqZjh4OzkcbRXT3fgy5jUta3CTf7JG01zk8EttM0UyMkinBVhyKxq4erR+NWNqOJpVv4buR0UoBZgACSeAB3robHwheXKB7h1t1PYjLflSpUKlV2grjrYinRV6jsc7RXZHwTDjAvm3f7g/wAaydR8LX1ihljxcRDklBgj8K2nga8FdxMKeYYeb5VIw6KKkgi8+4ihBwZHC59MnFcqTbsjsbSV2R0V1n/CESY/4/l/79//AF6a3gmUfdvoz9UI/rXZ/Z+I/lOL+0sN/N+ZytFb1x4R1OFS0YjmA7I2D+tYksUkEjRyoyOvVWGCK56lCpS+NWOiliKVX4JXGUUUVknY2avoej+HNV/tTTh5jZuIvlk9/Q/j/PNbFeYaLqbaVqKT8mM/LIo7r/8AW616ajrJGrowZWGQQcgivqsDifb0td1ufJY/C+wq6bPYdRRRXacIUUUUAFFFFABWB4p1X7Bp/kRNiefKjB5Ve5/p+PtW5NKkELyyMFRFLMT2Ary/VNQfU9QlunyAxwin+FR0H+e5NcGYYn2NOy3Z6GXYX29W72RTooor5c+rCuq8JaQk7NqE65VDiIEcE9zXK16HYn7D4QWROCtuZB9SM/1r0MupRlUcpbRVzzc0qyhSUI7ydjD1/wASTyXD2llIY4kO1nXqx9j2Fc00ju253Zm9Scmm0VzV8ROrJykzrw+Gp0YKMUaFjrV/YODFcMVH8Dncp/Dt+FR6pfDUtQe62bN4XK5zggAVToqXWnKPI3oUqFOM/aJanYeEdJTyjqU65JJEQPYdzWfrviOe8uHhtZWjtlOAUOC/vn0rpJs2HhE7OCtuAD7kYz+ted16OLm8PSjRhpdXZ5eDgsTWnWqa2dkODur7wzBvUHmup8N+IpvtCWN7IZEfiN2PIPofWuUpUZkdXU4ZTkH0NcOHxE6U1JM9HE4aFam4tHR+LNJSzuFvIF2xTHDKBwG/+vWJp/8AyErX/rsn8xXdeIFW68MySEc7Vce3IrhdP/5CVr/12T+YrrxlKMMSnHZ2ZxYGrKphZKW6ujsfGM0sOn25ikdCZcEqxHY1xov7xel3OPpIf8a6/wAa/wDINtv+uv8AQ1xFLMpyjX0YZXTjLD6rqbVh4n1GzkXzJTcRd1k6/n1rp7+ytfEekLcwAebt3Rt0IP8AdNefV2vgmYtZ3UJPCOGH4j/61XgK8qsnRqapkZhh40YqvS0aOLIKsVIIIOCDSVoa3EINbu0UYHmE/nz/AFrPrzakeSbj2PVpT54KXcK7Twfq3mwnTpm+eMboiT1XuPw/l9K4upba4ltLmO4hbbJG25TW+DxDoVVLp1OfG4ZYik49eh61RVbT72PUbGK6i+645Hoe4/OrNfWJpq6PkGnF2YUUUUxBRRVTUr+PTbCW5k52j5Vz949hSlJRV2OMXJ2RzfjHVcBdNibk4abH6D+v5Vx9STzyXM8k0rbpJGLMfeo6+SxeIdeq5dOh9hg8OsPSUevUKKKK5jrCvQ7QfbPB6onJa2KD6gY/mK88rrvCGqoobTpmxkloif1H9fzr0ctqRjUcJfaVjy80pSlSU4/ZdzkaK6HxB4entLmS5to2ktnO7CjJQ/T0rnq5K1GdKbjJHbQrwrQUosKKtWWm3eoSBLaBnz1bGFH1NLqVi2nXz2rOHZAMkDjJAP8AWp9nNR52tCvawc/Zp6nc3X+m+EWKclrcN+Qz/SvO67TwjqiS2x06ZhvTJjz/ABL6fhWLrugzabcPJFGz2rHKsBnb7GvSxkXXpQrQ10szysDNYetOhPS7ujFpQpZgoGSTgCkrpfDWgSzXSXt1GUhjO5AwwWPb8K4KFCdWajFHpYjEQo03KTN/XiLXwvLGTzsVB+YFcJp//IStf+uyfzFb/i/VVnmSxhbKxHMhHTd2H4Vgaf8A8hK1/wCuyfzFdmNqRniYqPSyODA0pQwspS63Z13jX/kG23/XX+hriK7jxorNp1vgE/vew9jXFCGVjhY3J9lNTmUW6+heVSSw+vdjK7TwREVtbqYjhnCj8B/9eufsPD+oX0gAgaKPPLyDAH4d66+6uLbw1oqwxkGQKRGp6s3qa0y+jKnL21TRIzzGvGpFUKbu2cZrsom1y8dTkeZj8uP6Vn0rMXYsxyxOSfU0lebUlzzcu56lKHJBR7IKKKKg0Oi8J6r9jvvskrfubg8f7L9vz6flXe15BXpHh3Vf7U04GRs3EXyye/ofx/nmvfyvE80fZS3Wx85m2F5Ze2js9zXooor2DxgrgPFeq/bb/wCyxN+4tyQcfxP3P4dPzrqPEWq/2XprFGxcS/LH7ep/D+eK83rx80xPLH2Ueu57WU4Xml7aWy2CiiivAPogooooAKUEqQQSCOQRSUUCOjsPF91bII7qMXCjgNnDf/XrQPi3S3O57GQt67FP9a4yiu2GYV4q17+pwzy3Dyd0reh1l140OzZZ2gQ9mkPT8B/jXMXNzNd3DzzuXkfqxqKisa2Jq1vjZtQwtKh8C1HI7RuroxV1OQQcEGumsfGU8SBLyATY/jU4P5dP5Vy9FKjiKlF3gx18NSrq1RXOzHizS1bethJv9di/zzWdqPi66uozFaxi3Q8Fgcsf8K52it54+vJWvb0MIZbQg+a1/UDzyakt5fIuYpsZ8tw2PXBzUdFcabTudrimrM7H/hN48f8AHi3/AH8/+tSN43X+HT8/WTH9K4+iu3+0cR3/AARw/wBl4b+X8WdJc+Mr6UEQRRQ57/eI/p+lYFxczXUxlnkaSQ9WY1FRXPVxFWr8budFHDUqPwRsFFFFYnQFFFFABWjouptpWopPyYj8sqjuv/1utZ1FXSqOnNTjujOrTjVg4S2Z66jrIiujBlYZBByCKdXK+D9W82E6dM3zxjdESeq+n4fy+ldVX19CrGtBTj1PjK9GVGo4S6HmOt6odU1J5gT5K/LEP9n1/HrWbuHrRRXytRupUcpH11JKlTUY7Cb19aN6+tFFVGjFkyryQu4etLkGiitVhoMxeKmhQCaURsegoorRYOm+5m8bUXYcIJD0X9RTxaTnon6iiitFgaXmZvH1fIcNPum6Rf8Ajw/xp40q9PSH/wAeH+NFFV/Z9LzIeY1eyJBomot0t/8Ax9f8aePD+qHpa/8AkRf8aKKpZdS7sl5lWXRDh4a1c9LT/wAiJ/jS/wDCNav/AM+f/kRP8aKKr+zaPdmP9rV+y/r5h/wjWr/8+f8A5ET/ABo/4RrV/wDnz/8AIif40UUf2bR8w/tav2X9fMP+Ea1j/nz/APIqf40f8I1rH/Pmf+/qf40UU/7No+Yf2tX7L+vmH/CNax/z5n/v6n+NH/CNax/z5H/v6n+NFFH9m0fMP7Wr+X9fMP8AhGtY/wCfI/8Af1P8aP8AhGtY/wCfI/8Af1P8aKKP7No+Yv7Wr+Qn/CNax/z5H/v6n+NH/CNax/z5H/v6n+NFFP8As2j5h/a2I8g/4RrWP+fI/wDf1P8AGj/hGtY/58j/AN/U/wAaKKP7No+Yv7WxHkTW2ha7Z3UdxDaESRtuB81P/iuldh9v1L/oESf9/wCP/GiiuvD4eNFNQbOTEYmVdqU0rn//2QplbmRzdHJlYW0KZW5kb2JqCjggMCBvYmogPDwvTGVuZ3RoIDEwNTYvRmlsdGVyL0ZsYXRlRGVjb2RlPj5zdHJlYW0KeJytV9122kYQvtdTbK+anjTL/kvqVWWjuE6C7IBM2tPTCyxkSoKEEdDk9I3zFp1d/YBkCyjy4Rx2Fmb2252d/WZmZV2EFldISheFU8sPrY8WQ+/0rxQR+Ohv6UoUJlbvLUWUofDBevVT+Fnrwhw+2cz68y9Qm+7mBDOJvpqViFlmeFXqcIaYq1BiCSKMtKik5hiZf6TtFjpaao6RXjBXKYT6EJWAi1KoD5E12u2+PDBBUdKcFkcizxyJ2RQ7joEzx8KUF1saHTYzW9TaiZkIl+h1jpoZNOHSHZqeHDErHJaj6ckeWrkymFVXyFruTzHEqYbm0jbSopKaY2T+EVwUOlpqjpFeMFcphPoQlYCLUqgP7fe3F8LMhtuhDPMqjm0dxvnR4KyvBtt0ow+u45qgWR7btdVqLmrD4catCrusBeci267X8aIjjC0wRK+QLlZuCQSuqiHddsRwCFwRxar+8Hfrh9ss/Xu53azj+8kkRUI5yHsBSC7aIRmlBH2af07jWZycg3XOpkzoKIZ3DEjqu/K/PX6fzjfxNut5D//G6TTO0C8n7W5lrRDBkgtb5TqYuYhSeNpSq6Le7w8U9ZfoI3DyCjGSYwpS05knM5Ir7W26VIAt52ZA7W8q6emm9k25jV2IYu60XwRnnFFiC8al6xLX5kBJhHCn4/1rV+vX0+pqACFdMYTESqe7vdBmjafzQzvGIVpmqsgARhLsKbOem16K9YqViTqy8j4hSeyACdwpvM/Sq7zBFHdXN2ed2C5TnhaO7upcRnBsDKnC7J09icTxtYe8t7953ofbuyDs3d5cByHq/+gHH/yxP/CD8AUoSR14CZ+u3wX+lT/oDiMIP3DO94tJuuldLuYx5Kmhf+lfj/0hurwZ3HrBHy/w7CST7clx5Ad9QAu8gf8SUMAsx6D+z8FWupLTP2vyhIWlcEpipE+JsVAAdGOlebEUACVnZGHbzEYFI0vk2OAeWhAy2xEyZTlt11We5ePif0DNjTRsJR2mYwIVmwvPy8WiKiqg7KknRk2/hDIocSBFdi0uuCkuGAwlnmoUS8t0A+FY4lSGhlkFVdoeLHcGF/E0m8w67suFmp86WFah4zSSA/tZUuTfDbvGKGHYdQ46INgm37MlmsYoWiaPm7jpCUYUVvJZVwzjL3E6T2fpNknirOtWpTzskwsfYgdSNQQph3LAcSjrCKmUTijClVC3tID66T/LOXgm3WQxyuLkfrnN1nGimesNGsdZpl2gCyVww1l5h+fdFlF522S6rSL9HLJiWitvf/ipvRbfa7UKqBNaLdPYlViNxu4kL1MoT4RuJ4CMWrx8/ziJvvx6/7hcb9DYGyEov8C9lzf91yMvfD3vd+1koLmw80HYbT2G17VfEhTzvLx2qnPSBggc5ySU/wA+MG5oCmVuZHN0cmVhbQplbmRvYmoKMSAwIG9iajw8L1BhcmVudCA5IDAgUi9Db250ZW50cyA4IDAgUi9UeXBlL1BhZ2UvUmVzb3VyY2VzPDwvWE9iamVjdDw8L2ltZzEgNiAwIFIvaW1nMCA0IDAgUi9YZjIgNyAwIFIvWGYxIDMgMCBSPj4vUHJvY1NldCBbL1BERiAvVGV4dCAvSW1hZ2VCIC9JbWFnZUMgL0ltYWdlSV0vRm9udDw8L0YxIDIgMCBSL0YyIDUgMCBSPj4+Pi9NZWRpYUJveFswIDAgODQyIDU5NV0+PgplbmRvYmoKMTAgMCBvYmpbMSAwIFIvWFlaIDAgNjA3IDBdCmVuZG9iago1IDAgb2JqPDwvQmFzZUZvbnQvSGVsdmV0aWNhLUJvbGQvVHlwZS9Gb250L0VuY29kaW5nL1dpbkFuc2lFbmNvZGluZy9TdWJ0eXBlL1R5cGUxPj4KZW5kb2JqCjIgMCBvYmo8PC9CYXNlRm9udC9IZWx2ZXRpY2EvVHlwZS9Gb250L0VuY29kaW5nL1dpbkFuc2lFbmNvZGluZy9TdWJ0eXBlL1R5cGUxPj4KZW5kb2JqCjMgMCBvYmogPDwvVHlwZS9YT2JqZWN0L1Jlc291cmNlczw8L1Byb2NTZXQgWy9QREYgL1RleHQgL0ltYWdlQiAvSW1hZ2VDIC9JbWFnZUldPj4vU3VidHlwZS9Gb3JtL0JCb3hbMCAwIDM3NCAzMV0vTWF0cml4IFsxIDAgMCAxIDAgMF0vTGVuZ3RoIDExMDAvRm9ybVR5cGUgMS9GaWx0ZXIvRmxhdGVEZWNvZGU+PnN0cmVhbQp4nG2YS47lNgxF517FW4JJyrK8hQAZZJRBkFnQHQSdAJ1Jth+7dQ9VhUcUUCyUeXT1vaL9fdtfYa+/77C/vm1xtoz3v79tf26/bv9s9vpv89dPd9Jfm+2vn7ffft9ff2zf7wfPz79facX3iflsjt9q6suPf+xPvgNIx6euOP+ApMRMvZEupM/cRihUupAhZMzcTihUxkTC58M77h/IUajM1BvRw5i5Imnok0pIpelhU+4gvKs0qTT1us1BiGyVStPwDz08lNsJ7yqHVA49PGauyKNSOVBRr485CMhSRcPvaq+r+UF4V+nqWNc697nsIrsXKl0bpqu9PpuHbJWKOsb213GArNbllMqph+fMFXlWKqdUhrb5mLseslIZOi9D7Y3ZvMhRnZehjg2twJgLAlmdl6GlvNTepeY74V3lUscutXfN5kVe1dm/1LFL7V2zechqxi51zHYt9PPH/oGtdpmSH6pBTQ3obO6TmNIfbsAN5beMlZ7W1UzDfv7YP/HV1lb6w6Fnyoe3ah6VfnPOrLjGlXyp58wLDmhyxOS9Ok+GnRo2aLLFxVdbxPBUC+YlNK7kqz2v9IejnyEd+Kg2vtIfjn7KWBdf7hdc2XBTk7suvpxPrNka+6xpn8C3cj4b++xgXg6NK/nqMCj94ein/Dn5o9wvmLvhsCbHXXx5HrBrwzNNHrr4cv0wYMM4TUaafGnChgvbybycGlfypd7JvJzMy6lxwZ/lfjmZF9za5N6LL9cP6zcs22Thiy/Hh/8bvm2D/o2MhR6XgA30hvLhy4tA6TeHHdtF/shY6OHthiebPDr5q9TD4A1bNtn04kv/xOUde3X8NvmyZMOvfac03FXh7S3ju57SH476UD69+FpP8+nYsht168hY6OHzbg6n6hLeysLXHI5+yt8XX+1P535w7Nyd/JGx0ON+8KybKX/hy/vB8Xmn5nWq5+RLPQpoD9Y9tG7wUeoF644tu3x68dX5c3zeG+vXNP/Jl+vX8qWF8cnfk2/1iwvjo+T2xruLZ6z02J/YuR/o9IyFHveDY+cuf0++vB+c+8EP1uHQPCZf3Q9Kf97M0Ovkt4yFHjW993yl483MMxZ6nXnhGvDOi13PWOmx7ti5n+iMjIUe94Of9POUDnx5Pyj9ef1k/eTTi69fQVk/inRX0Z78KNePit+p1F2V++LL80DZ7xfzcvHW2zMWehfzgi27fDr5q1w/fD4ov2Mnv2d81wvq+cDOQ/4OH2U9H9wPgZ2H/H3x1f6MPfvJm738ffHV+gX3Q2DnIX9ffPkdgfshjH6adODL+0HpD0c/5e+LL+eT+yGw85C/L746D8H9ENh5OJ88PGOhl99W8uNKfiMZGQu9/MCSX1j4UAJf3g+BzwdldKiuXnypR10e2GvIb5Nv5X7Br6PRzyad5Mv90ugnthzy6cWX+wWfD+w15LfJH+V+wa/zK19+9jtWTO6X++d/50PmQwplbmRzdHJlYW0KZW5kb2JqCjcgMCBvYmogPDwvVHlwZS9YT2JqZWN0L1Jlc291cmNlczw8L1Byb2NTZXQgWy9QREYgL1RleHQgL0ltYWdlQiAvSW1hZ2VDIC9JbWFnZUldPj4vU3VidHlwZS9Gb3JtL0JCb3hbMCAwIDI2NCAyMF0vTWF0cml4IFsxIDAgMCAxIDAgMF0vTGVuZ3RoIDc2MC9Gb3JtVHlwZSAxL0ZpbHRlci9GbGF0ZURlY29kZT4+c3RyZWFtCnicbZfNjhQxDITv/RR5hNj5fwUkDpw4IG4IEAKk5cLr002q3DOa0krr1bY/l8dJatJvR06e068z5PTz8F4j+vXH9+Pj8fuw9Pfw9O5M+nFYTu+PT59z+nK8nQ+unz/fWMXzxnyX42+U+vr/H/nKdwLQcehmhkBCYqeeSAfS0yNZlUoHMoHM9Eh2pTI3Unw/POMjOYXKTj0RPCw7FyQLPakUqFQ8rMidDK8qFSoVD+vOBVmVSoVKwzjbni5JpdKwLg3jbHu6IJtal4Z1aajXdnmSal0aGuuo11F+MryqdDTGjYmNCrKrPdbRWEe9vsuTrEoFjQ2Mc+zpklQTG1iXgXpjlwc51LoMNDbR9dwfgqSa2MTHn6g3d3mQU63LRGMT23zuXU/SlQrOy0K9hfKd4VVlobGFemuXB7nUuiw0tlBv7fIk1cQWGrOMgtcfj2wXOki+qEpqj5h0lHsSQ/rFTXIzPfNqjZB+csYujTozotAz9mlObs8ieFNzRPrFdXI9PfNqmEg/OfqsOXV6RKFH0zY6p8FJg5fGbbRhK9QrzO8RhV6hXuH6FcyffJF6hetX2GeBTvBy/Qr7pJsa3PXm1REwWrNV9lmhQ76q04b0i2OfFTrBK5NC+sk19tmo0yMKvcY+G/Ua8sk3uV9a6PE8wKlvXuvxPNBrDd578/I80LiNhmsw4OC7XD+6tw3us4F9ErzUG9xntF6DFQc/5Pmjj9tgnwM6wcv1G+yTJmww5ZtX3zRGR7fJPid0yE+5fpN90o4N9nzzcp70dqMnGzw6+CXPHw3eaMsGm755eR7C5RfnuTCP4OX5W5in086d/h68mqfz+8Gzk8NtL+eIr3pIvzjeK+HvN68+n/P7wWnLbsyfEYUefd6NeoZ88ib1jHpOPWf+jCj0nHpxb+b1l7xLPd6dnfbq8Nubl3r0a6e9Ovw2eOnXTr92XpgdF+ibl1d83r69cr9UrHfwcr/UeGmJtxa+fOSIQo8+H29V8ZpV7xjch/PnH6hDnvAKZW5kc3RyZWFtCmVuZG9iago5IDAgb2JqPDwvVHlwZS9QYWdlcy9Db3VudCAxL0tpZHNbMSAwIFJdPj4KZW5kb2JqCjExIDAgb2JqPDwvTmFtZXNbKEpSX1BBR0VfQU5DSE9SXzBfMSkgMTAgMCBSXT4+CmVuZG9iagoxMiAwIG9iajw8L0Rlc3RzIDExIDAgUj4+CmVuZG9iagoxMyAwIG9iajw8L05hbWVzIDEyIDAgUi9UeXBlL0NhdGFsb2cvUGFnZXMgOSAwIFI+PgplbmRvYmoKMTQgMCBvYmo8PC9DcmVhdG9yKEphc3BlclJlcG9ydHMgXChlc29fbGFiZWxfcHJvXCkpL1Byb2R1Y2VyKGlUZXh0IDIuMC42IFwoYnkgbG93YWdpZS5jb21cKSkvTW9kRGF0ZShEOjIwMTYwMzE4MTQxODA4KzAxJzAwJykvQ3JlYXRpb25EYXRlKEQ6MjAxNjAzMTgxNDE4MDgrMDEnMDAnKT4+CmVuZG9iagp4cmVmCjAgMTUKMDAwMDAwMDAwMCA2NTUzNSBmIAowMDAwMDA3Mjg0IDAwMDAwIG4gCjAwMDAwMDc2MzAgMDAwMDAgbiAKMDAwMDAwNzcxNyAwMDAwMCBuIAowMDAwMDAwMDE1IDAwMDAwIG4gCjAwMDAwMDc1MzggMDAwMDAgbiAKMDAwMDAwMDk2OSAwMDAwMCBuIAowMDAwMDA5MDE5IDAwMDAwIG4gCjAwMDAwMDYxNjAgMDAwMDAgbiAKMDAwMDAwOTk4MCAwMDAwMCBuIAowMDAwMDA3NTAzIDAwMDAwIG4gCjAwMDAwMTAwMzAgMDAwMDAgbiAKMDAwMDAxMDA4NSAwMDAwMCBuIAowMDAwMDEwMTE4IDAwMDAwIG4gCjAwMDAwMTAxNzYgMDAwMDAgbiAKdHJhaWxlcgo8PC9Sb290IDEzIDAgUi9JRCBbPDcyZjdhMjlkNTgzZGMzNjhmNzUyMmZhMTRkNzhhMTRjPjxmMTZlOTRjZGFjMmMzYTRiZDNlMGUyOTUxMjczYTQ0NT5dL0luZm8gMTQgMCBSL1NpemUgMTU+PgpzdGFydHhyZWYKMTAzNDkKJSVFT0YK
BYTES;

    }
}
