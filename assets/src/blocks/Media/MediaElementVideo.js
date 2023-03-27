export const MediaElementVideo = ({videoURL, videoPoster}) => {
  return <div className="video-embed">
    <div className="video-embed-container">
      <video
        src={videoURL}
        style={{
          width: '100%',
          height: '100%',
        }}
        className="mejs-video-block"
        poster={videoPoster}
        controls="controls"
        preload="metadata">
      </video>
    </div>
  </div>;
};
