module.exports = {
	docs: {
		files: [
			{
				cwd:'protected/assets',
				expand: true,
				src: ['{css,img,js}/**/*'],
				dest: 'docs/public/assets'
			},
			{
				cwd:'protected/assets',
				expand: true,
				src: ['components/**/*'],
				dest: 'docs/public/assets'
			},
			{
				cwd: 'protected',
				expand: true,
				src: ['modules/**/assets/**/*'],
				dest: 'docs/public/assets'
			},
			{
				cwd: 'protected',
				expand: true,
				src: ['modules/eyedraw/{css,img}/**/*'],
				dest: 'docs/public/assets'
			},
			{
				cwd: 'docs/src/',
				expand: true,
				src: ['fragments/**/*'],
				dest: 'docs/public/'
			},
			{
				cwd: 'docs/src/',
				expand: true,
				src: ['static-templates/**/*'],
				dest: 'docs/public/'
			},
			{
				cwd: 'docs/src/assets',
				expand: true,
				src: ['{js,css,img}/**/*'],
				dest: 'docs/public/assets'
			},
			{
				cwd: 'docs/src/',
				expand: true,
				src: ['*.php'],
				dest: 'docs/public/'
			}
		]
	}
};