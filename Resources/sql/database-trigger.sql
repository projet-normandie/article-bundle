-- Comment
delimiter //
DROP TRIGGER IF EXISTS `articleCommentAfterInsert`//
CREATE TRIGGER articleCommentAfterInsert AFTER INSERT ON article_comment
FOR EACH ROW
BEGIN
	UPDATE article
	SET nbComment = (SELECT COUNT(id) FROM article_comment WHERE idArticle = NEW.idArticle)
	WHERE id = NEW.idArticle;
END //
delimiter ;

delimiter //
DROP TRIGGER IF EXISTS `articleCommentAfterDelete`//
CREATE TRIGGER articleCommentAfterDelete AFTER DELETE ON article_comment
FOR EACH ROW
BEGIN
	UPDATE article
	SET nbComment = (SELECT COUNT(id) FROM article_comment WHERE idArticle = OLD.idArticle)
	WHERE id = OLD.idArticle;
END //
delimiter ;

